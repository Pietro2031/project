<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id == 0) {
    die('Invalid order ID');
}

// Fetch the specific order details
$query = "
SELECT orders.id, user_account.username, orders.order_date, orders.total_amount, orders.payment_method
FROM orders
LEFT JOIN user_account ON orders.user_id = user_account.id
WHERE orders.id = $order_id
";
$result = mysqli_query($conn, $query);

$order = mysqli_fetch_assoc($result);
if (!$order) {
    die('Order not found');
}

// Create PDF
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

// Title and header
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Payment History', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');
$pdf->Ln(10);

// Table headers
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(30, 8, 'Order ID', 1, 0, 'C');
$pdf->Cell(50, 8, 'Customer', 1, 0, 'C');
$pdf->Cell(40, 8, 'Order Date', 1, 0, 'C');
$pdf->Cell(40, 8, 'Total Amount', 1, 0, 'C');
$pdf->Cell(30, 8, 'Payment', 1, 1, 'C');

// Order details
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(30, 8, $order['id'], 1, 0, 'C');
$pdf->Cell(50, 8, $order['username'], 1, 0, 'C');
$pdf->Cell(40, 8, date('F j, Y', strtotime($order['order_date'])), 1, 0, 'C');
$pdf->Cell(40, 8, 'Php ' . number_format($order['total_amount'], 2), 1, 0, 'C');
$pdf->Cell(30, 8, $order['payment_method'], 1, 1, 'C');

// Output the PDF
$pdf->Output('payment_history_order_' . $order['id'] . '.pdf', 'I');
?>
