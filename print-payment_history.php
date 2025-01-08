<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');

class MYPDF extends TCPDF {}


$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Peter Beans System');
$pdf->SetTitle('Payment History');
$pdf->SetSubject('Generated Report');

$pdf->SetMargins(10, 30, 20);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(20);
$pdf->SetAutoPageBreak(TRUE, 25);


$pdf->AddPage();


$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Payment History', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');

$pdf->Ln(10);


$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(30, 8, 'Order ID', 1, 0, 'C');
$pdf->Cell(50, 8, 'Customer', 1, 0, 'C');
$pdf->Cell(40, 8, 'Order Date', 1, 0, 'C');
$pdf->Cell(40, 8, 'Total Amount', 1, 0, 'C');
$pdf->Cell(30, 8, 'Payment', 1, 1, 'C');


$query = "
SELECT orders.id, user_account.username, orders.order_date, orders.total_amount, orders.payment_method 
FROM orders
LEFT JOIN user_account ON orders.user_id = user_account.id
WHERE orders.status = 1
ORDER BY orders.order_date DESC
";

$result = mysqli_query($conn, $query);

$total_sum = 0;


$pdf->SetFont('helvetica', '', 11);
while ($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(30, 8, $row['id'], 1, 0, 'C');
    $pdf->Cell(50, 8, $row['username'], 1, 0, 'C');
    $pdf->Cell(40, 8, date('F j, Y', strtotime($row['order_date'])), 1, 0, 'C');
    $pdf->Cell(40, 8, 'Php ' . number_format($row['total_amount'], 2), 1, 0, 'C');
    $pdf->Cell(30, 8, $row['payment_method'], 1, 1, 'C');


    $total_sum += $row['total_amount'];
}


$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(120, 10, 'Total Amount', 1, 0, 'R');
$pdf->Cell(40, 10, 'Php ' . number_format($total_sum, 2), 1, 0, 'C');
$pdf->Cell(30, 10, '', 1, 1, 'C');


$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Report generated using Peter Beans System. All rights reserved.', 0, 1, 'C');


$pdf->Output('payment_history.pdf', 'I');
