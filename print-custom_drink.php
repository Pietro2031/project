<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');

// Fetch filter and sort options
$timeFrame = isset($_GET['time_frame']) ? $_GET['time_frame'] : 'any';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

// Build the SQL query with optional filter and sort
$query = "SELECT * FROM custom_drink";

if ($timeFrame === '3day') {
    $query .= " WHERE order_date >= NOW() - INTERVAL 3 DAY";
} elseif ($timeFrame === '7day') {
    $query .= " WHERE order_date >= NOW() - INTERVAL 7 DAY";
} elseif ($timeFrame === '1month') {
    $query .= " WHERE order_date >= NOW() - INTERVAL 1 MONTH";
}

$query .= " ORDER BY total_price $sortOrder, order_date DESC";

$result = $conn->query($query);

// Create PDF instance
class MYPDF extends TCPDF {}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your System Name');
$pdf->SetTitle('Custom Drink Report');
$pdf->SetSubject('Generated Report');
$pdf->SetMargins(10, 30, 20);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(20);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

// Add title and metadata
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Custom Drink Report', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');
$pdf->Ln(10);

// Table header
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(18, 8, 'Order ID', 1, 0, 'C');
$pdf->Cell(22, 8, 'Customer ID', 1, 0, 'C');
$pdf->Cell(30, 8, 'Base', 1, 0, 'C');
$pdf->Cell(40, 8, 'Ingredients', 1, 0, 'C');
$pdf->Cell(25, 8, 'Total Price', 1, 0, 'C');
$pdf->Cell(25, 8, 'Payment Method', 1, 0, 'C');
$pdf->Cell(30, 8, 'Order Date', 1, 1, 'C');

// Table rows
$pdf->SetFont('helvetica', '', 8);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(18, 8, $row['id'], 1, 0, 'C');
    $pdf->Cell(22, 8, $row['customer_id'], 1, 0, 'C');
    $pdf->Cell(30, 8, $row['base'], 1, 0, 'C');
    $pdf->Cell(40, 8, $row['ingredients'], 1, 0, 'C');
    $pdf->Cell(25, 8, '₱' . number_format($row['total_price'], 2), 1, 0, 'C');
    $pdf->Cell(25, 8, $row['payment_method'], 1, 0, 'C');
    $pdf->Cell(30, 8, date('F j, Y', strtotime($row['order_date'])), 1, 1, 'C');
}

// Footer
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Report generated using Peter Beans System. All rights reserved.', 0, 1, 'C');

// Output PDF
$pdf->Output('custom_drink_report.pdf', 'I');

$conn->close();
