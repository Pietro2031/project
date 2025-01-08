<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');


class MYPDF extends TCPDF {}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your System');
$pdf->SetTitle('Product Report');
$pdf->SetSubject('Generated Report');


$pdf->SetMargins(10, 30, 20);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(20);
$pdf->SetAutoPageBreak(TRUE, 25);


$pdf->AddPage();


$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Product Report', 0, 1, 'C');


$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');


$pdf->Ln(10);


$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(40, 8, 'Product Name', 1, 0, 'C');
$pdf->Cell(40, 8, 'Category', 1, 0, 'C');
$pdf->Cell(80, 8, 'Description', 1, 0, 'C');
$pdf->Cell(30, 8, 'Price', 1, 1, 'C');


$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'ASC';

$query = "SELECT p.product_name, c.category_name, p.product_description, p.price 
          FROM coffee_products p 
          JOIN coffee_category c ON p.category_id = c.id";

if ($categoryFilter !== '') {
    $query .= " WHERE p.category_id = ?";
}
$query .= " ORDER BY p.price $sortOrder";

$stmt = $conn->prepare($query);
if ($categoryFilter !== '') {
    $stmt->bind_param("i", $categoryFilter);
}
$stmt->execute();
$result = $stmt->get_result();


$pdf->SetFont('helvetica', '', 11);

while ($row = $result->fetch_assoc()) {
    
    $cellData = [
        ['width' => 40, 'text' => $row['product_name'], 'align' => 'C'],
        ['width' => 40, 'text' => $row['category_name'], 'align' => 'C'],
        ['width' => 80, 'text' => $row['product_description'], 'align' => 'L'],
        ['width' => 30, 'text' => 'Php ' . number_format($row['price'], 2), 'align' => 'C'],
    ];

    
    $maxHeight = 0;
    foreach ($cellData as $cell) {
        $cellHeight = $pdf->getStringHeight($cell['width'], $cell['text']);
        if ($cellHeight > $maxHeight) {
            $maxHeight = $cellHeight;
        }
    }

    
    foreach ($cellData as $cell) {
        $pdf->MultiCell($cell['width'], $maxHeight, $cell['text'], 1, $cell['align'], 0, 0);
    }

    
    $pdf->Ln();
}

$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Report generated using Peter Beans System. All rights reserved.', 0, 1, 'C');


$pdf->Output('product_report.pdf', 'I');


$stmt->close();
$conn->close();
