<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\FinancialReportController;
use App\Http\Controllers\Auth\ManagerFinancialReportController;
use App\Http\Controllers\Auth\ManagerTransactionReportController;
use App\Http\Controllers\Auth\ManagerUtilityTrackingController;
use App\Http\Controllers\Auth\ServiceHistoryController;
use App\Http\Controllers\Auth\UtilityTrackingController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReportSortingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->string('username')->nullable();
            $table->string('role');
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('ref_number');
            $table->string('service_type')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('order_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('expense_type');
            $table->decimal('amount', 10, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->date('date')->nullable();
            $table->timestamp('date_logged')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        DB::table('branches')->insert(['id' => 1, 'branch_name' => 'Carmona']);
        DB::table('users')->insert([
            ['id' => 1, 'fullname' => 'Admin User', 'role' => 'admin', 'branch_id' => null],
            ['id' => 2, 'fullname' => 'Manager User', 'role' => 'manager', 'branch_id' => 1],
            ['id' => 10, 'fullname' => 'Zoe Customer', 'role' => 'customer', 'branch_id' => 1],
            ['id' => 11, 'fullname' => 'Anna Customer', 'role' => 'customer', 'branch_id' => 1],
        ]);
    }

    public function test_admin_financial_report_can_sort_transactions_by_amount(): void
    {
        $this->addTransaction(1, 10, '2026-10-01', 100);
        $this->addTransaction(2, 11, '2026-10-02', 300);

        $this->actingAs($this->user(1, 'admin', null));
        $response = app(FinancialReportController::class)->index($this->request([
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'sort_by' => 'amount_desc',
        ]));

        $this->assertSame([2, 1], $response->getData()['list_q']->pluck('id')->all());
    }

    public function test_manager_financial_report_can_sort_customers_by_name(): void
    {
        $this->addTransaction(1, 10, '2026-10-01', 100);
        $this->addTransaction(2, 11, '2026-10-02', 300);

        $this->actingAs($this->user(2, 'manager', 1));
        $response = app(ManagerFinancialReportController::class)->index($this->request([
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'sort_by' => 'name_asc',
        ]));

        $this->assertSame([2, 1], $response->getData()['transactions']->pluck('id')->all());
    }

    public function test_admin_utility_report_can_sort_expense_types(): void
    {
        $this->addExpense(1, 'Water', 200, '2026-10-01');
        $this->addExpense(2, 'Electricity', 300, '2026-10-02');

        $this->actingAs($this->user(1, 'admin', null));
        $response = app(UtilityTrackingController::class)->index($this->request([
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
            'sort_by' => 'type_asc',
        ]));

        $this->assertSame([2, 1], $response->getData()['logs']->pluck('id')->all());
    }

    public function test_manager_utility_report_can_sort_by_amount(): void
    {
        $this->addExpense(1, 'Water', 200, '2026-10-01');
        $this->addExpense(2, 'Electricity', 300, '2026-10-02');

        $this->actingAs($this->user(2, 'manager', 1));
        $response = app(ManagerUtilityTrackingController::class)->index($this->request([
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
            'sort_by' => 'amount_desc',
        ]));

        $this->assertSame([2, 1], $response->getData()['logs']->pluck('id')->all());
    }

    public function test_financial_reports_can_export_only_selected_result_pages(): void
    {
        for ($id = 1; $id <= 25; $id++) {
            $this->addTransaction($id, 10, '2026-10-01', $id * 10);
        }

        foreach ([
            [FinancialReportController::class, 1, 'admin', null, 'list_q'],
            [ManagerFinancialReportController::class, 2, 'manager', 1, 'transactions'],
        ] as [$controllerClass, $userId, $role, $branchId, $recordsKey]) {
            $this->actingAs($this->user($userId, $role, $branchId));
            $response = app($controllerClass)->index($this->request([
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-31',
                'export_mode' => 'pdf',
                'export_pages' => '2',
            ]));

            $this->assertSame([5, 4, 3, 2, 1], $response->getData()[$recordsKey]->pluck('id')->all());
            $this->assertSame(25, $response->getData()['totalRecords']);
            $this->assertSame(2, $response->getData()['pageCount']);
        }

        $this->actingAs($this->user(1, 'admin', null));
        $allResults = app(FinancialReportController::class)->index($this->request([
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'export_mode' => 'pdf',
            'export_pages' => 'all',
        ]));
        $this->assertCount(25, $allResults->getData()['list_q']);
    }

    public function test_report_export_rejects_page_numbers_beyond_the_result_page_count(): void
    {
        for ($id = 1; $id <= 25; $id++) {
            $this->addTransaction($id, 10, '2026-10-01', $id * 10);
        }

        $this->actingAs($this->user(1, 'admin', null));
        $this->expectException(ValidationException::class);

        app(FinancialReportController::class)->index($this->request([
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'export_mode' => 'print',
            'export_pages' => '3',
        ]));
    }

    public function test_utility_reports_can_export_only_selected_result_pages(): void
    {
        for ($id = 1; $id <= 25; $id++) {
            $this->addExpense($id, 'Water', $id * 10, '2026-10-01');
        }

        foreach ([
            [UtilityTrackingController::class, 1, 'admin', null],
            [ManagerUtilityTrackingController::class, 2, 'manager', 1],
        ] as [$controllerClass, $userId, $role, $branchId]) {
            $this->actingAs($this->user($userId, $role, $branchId));
            $response = app($controllerClass)->index($this->request([
                'date_from' => '2026-10-01',
                'date_to' => '2026-10-31',
                'export_mode' => 'print',
                'export_pages' => '2',
            ]));

            $this->assertSame([5, 4, 3, 2, 1], $response->getData()['logs']->pluck('id')->all());
            $this->assertSame(25, $response->getData()['totalRecords']);
            $this->assertSame(2, $response->getData()['pageCount']);
        }
    }

    public function test_transaction_reports_paginate_and_export_selected_result_pages(): void
    {
        for ($id = 1; $id <= 25; $id++) {
            $this->addTransaction($id, 10, '2026-10-01', $id * 10);
        }

        foreach ([
            [ServiceHistoryController::class, 1, 'admin', null, '/admin/transaction-report', 'admin.transaction.report'],
            [ManagerTransactionReportController::class, 2, 'manager', 1, '/manager/transaction-report', 'manager.transaction.report'],
        ] as [$controllerClass, $userId, $role, $branchId, $uri, $routeName]) {
            $this->actingAs($this->user($userId, $role, $branchId));
            $request = $this->routeRequest($uri, $routeName, [
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-31',
            ]);
            $response = app($controllerClass)->index($request);

            $records = $response->getData()['list'];
            $this->assertInstanceOf(LengthAwarePaginator::class, $records);
            $this->assertSame(20, $records->perPage());
            $this->assertSame(25, $records->total());

            $exportRequest = $this->routeRequest($uri, $routeName, [
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-31',
                'export_mode' => 'pdf',
                'export_pages' => '2',
            ]);
            $export = app($controllerClass)->index($exportRequest);
            $this->assertSame([5, 4, 3, 2, 1], $export->getData()['list']->pluck('id')->all());
        }
    }

    private function request(array $query): Request
    {
        return Request::create('/', 'GET', $query);
    }

    private function routeRequest(string $uri, string $routeName, array $query): Request
    {
        $request = Request::create($uri, 'GET', $query);
        $request->setRouteResolver(static function () use ($uri, $routeName) {
            return (new Route('GET', $uri, []))->name($routeName);
        });

        return $request;
    }

    private function addTransaction(int $id, int $userId, string $date, float $amount): void
    {
        DB::table('transactions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'branch_id' => 1,
            'ref_number' => 'LC-' . $id,
            'service_type' => 'Wash-Dry',
            'total_amount' => $amount,
            'order_status' => 'Claimed',
            'payment_status' => 'Paid',
            'created_at' => $date . ' 12:00:00',
        ]);
    }

    private function addExpense(int $id, string $type, float $amount, string $date): void
    {
        DB::table('expenses')->insert([
            'id' => $id,
            'branch_id' => 1,
            'expense_type' => $type,
            'amount' => $amount,
            'remarks' => '',
            'date' => $date,
            'date_logged' => $date . ' 12:00:00',
            'created_at' => $date . ' 12:00:00',
        ]);
    }

    private function user(int $id, string $role, ?int $branchId): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $id,
            'role' => $role,
            'branch_id' => $branchId,
        ]);

        return $user;
    }
}
