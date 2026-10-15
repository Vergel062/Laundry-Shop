<?php
$zipFile1 = __DIR__ . '/../storage/app/public/payment_proofs.zip';
$zipFile2 = __DIR__ . '/../storage/app/public/profile_pics.zip';
$extractTo = __DIR__ . '/../storage/app/public/';

$output = "";

if (file_exists($zipFile1)) {
    $zip = new ZipArchive;
    if ($zip->open($zipFile1) === TRUE) {
        $zip->extractTo($extractTo);
        $zip->close();
        $output .= "Tagumpay! Na-extract ang payment_proofs.<br>";
    } else {
        $output .= "May problema sa pag-extract ng payment_proofs.<br>";
    }
} else {
    $output .= "Hindi makita ang payment_proofs.zip sa storage/app/public/.<br>";
}

if (file_exists($zipFile2)) {
    $zip = new ZipArchive;
    if ($zip->open($zipFile2) === TRUE) {
        $zip->extractTo($extractTo);
        $zip->close();
        $output .= "Tagumpay! Na-extract ang profile_pics.<br>";
    } else {
        $output .= "May problema sa pag-extract ng profile_pics.<br>";
    }
} else {
    $output .= "Hindi makita ang profile_pics.zip sa storage/app/public/.<br>";
}

echo "<h2>" . $output . "</h2>";
echo "<p>Puwede mo na itanggal ang extract.php pagkatapos nito para sa seguridad.</p>";
?>