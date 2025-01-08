<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');

// Initialize TCPDF
class MYPDF extends TCPDF {}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Document metadata
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your System');
$pdf->SetTitle('Product Report');
$pdf->SetSubject('Generated Report');

// Margins and page settings
$pdf->SetMargins(10, 30, 20);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(20);
$pdf->SetAutoPageBreak(TRUE, 25);

// Add a page
$pdf->AddPage();

// Report title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Product Report', 0, 1, 'C');

// Generated date
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');

// Add spacing
$pdf->Ln(10);

// Table headers
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(40, 8, 'Product Name', 1, 0, 'C');
$pdf->Cell(40, 8, 'Category', 1, 0, 'C');
$pdf->Cell(80, 8, 'Description', 1, 0, 'C');
$pdf->Cell(30, 8, 'Price', 1, 1, 'C');

// Fetch products from the database
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

// Table data
$pdf->SetFont('helvetica', '', 11);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(40, 8, $row['product_name'], 1, 0, 'C');
    $pdf->Cell(40, 8, $row['category_name'], 1, 0, 'C');
    $pdf->Cell(80, 8, $row['product_description'], 1, 0, 'C');
    $pdf->Cell(30, 8, '₱' . number_format($row['price'], 2), 1, 1, 'C');
}

// Footer text
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Report generated using Your System. All rights reserved.', 0, 1, 'C');

// Output PDF
$pdf->Output('product_report.pdf', 'I');

// Close database connection
$stmt->close();
$conn->close();
