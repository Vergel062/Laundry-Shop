<div class="print-document-header report-print-header d-none" style="flex-direction: column !important; align-items: center !important; justify-content: center !important; gap: 3px !important; text-align: center !important; border: 0 !important; padding: 0 0 10px !important; margin-bottom: 14px !important;">
    <div class="report-print-title" style="color: #334155 !important; font-size: 8pt !important; font-weight: 700 !important; letter-spacing: 0.5px !important; line-height: 1.2 !important; text-transform: uppercase !important;">{{ $reportTitle }}</div>
    <div class="print-brand" style="color: #0f172a !important; font-size: 20pt !important; font-weight: 800 !important; letter-spacing: -0.5px !important; line-height: 1.1 !important;">LaundryCare</div>
    <div class="report-print-rule" style="width: 100% !important; border-top: 1px solid #cbd5e1 !important; margin-top: 5px !important;"></div>
    <div class="report-print-meta" style="display: flex !important; justify-content: space-between !important; gap: 8px !important; width: 100% !important; margin-top: 3px !important; color: #475569 !important; font-size: 6pt !important; text-align: left !important;">
        <span><strong>Branch:</strong> {{ strtoupper($branchLabel) }}</span>
        <span><strong>Generated Date:</strong> <span class="printGeneratedDate">{{ $generatedDate }}</span></span>
        <span><strong>Filter Period:</strong> {{ $filterPeriod }}</span>
    </div>
</div>
