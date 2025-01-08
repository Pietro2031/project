<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');


$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';


$query = "SELECT o.id, o.user_id, o.order_date, o.total_amount, o.order_quantity, o.product_ids, o.status, o.payment_method, o.flavor, o.toppings 
          FROM orders o";

if ($statusFilter !== '') {
    $query .= " WHERE o.status = ?";
}

$query .= " ORDER BY o.order_date $sortOrder";

$stmt = $conn->prepare($query);

if ($statusFilter !== '') {
    $stmt->bind_param("i", $statusFilter);
}

$stmt->execute();
$result = $stmt->get_result();


class MYPDF extends TCPDF {}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your System Name');
$pdf->SetTitle('Order Report');
$pdf->SetSubject('Generated Report');
$pdf->SetMargins(10, 30, 20);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(20);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();


$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Order Report', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');
$pdf->Ln(10);


$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(18, 8, 'Order ID', 1, 0, 'C');
$pdf->Cell(22, 8, 'User ID', 1, 0, 'C');
$pdf->Cell(30, 8, 'Order Date', 1, 0, 'C');
$pdf->Cell(25, 8, 'Total Amount', 1, 0, 'C');
$pdf->Cell(20, 8, 'Quantity', 1, 0, 'C');
$pdf->Cell(30, 8, 'Products', 1, 0, 'C');
$pdf->Cell(18, 8, 'Status', 1, 0, 'C');
$pdf->Cell(20, 8, 'Payment', 1, 0, 'C');
$pdf->Cell(18, 8, 'Flavor', 1, 0, 'C');
$pdf->Cell(18, 8, 'Toppings', 1, 1, 'C');


$pdf->SetFont('helvetica', '', 8);
while ($row = $result->fetch_assoc()) {
    $statusText = $row['status'] == 1 ? 'Completed' : ($row['status'] == 2 ? 'Cancelled' : 'Pending');

    
    $pdf->Cell(18, 8, $row['id'], 1, 0, 'C');
    $pdf->Cell(22, 8, $row['user_id'], 1, 0, 'C');
    $pdf->Cell(30, 8, date('F j, Y', strtotime($row['order_date'])), 1, 0, 'C');
    $pdf->Cell(25, 8, '₱' . number_format($row['total_amount'], 2), 1, 0, 'C');
    $pdf->Cell(20, 8, $row['order_quantity'], 1, 0, 'C');
    $pdf->Cell(30, 8, $row['product_ids'], 1, 0, 'C');
    $pdf->Cell(18, 8, $statusText, 1, 0, 'C');
    $pdf->Cell(20, 8, $row['payment_method'], 1, 0, 'C');
    $pdf->Cell(18, 8, $row['flavor'], 1, 0, 'C');
    $pdf->Cell(18, 8, $row['toppings'], 1, 1, 'C');
}


$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Report generated using Peter Beans System. All rights reserved.', 0, 1, 'C');


$pdf->Output('order_report.pdf', 'I');

$stmt->close();
$conn->close();
?>
