<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');

// Fetch filter values from URL parameters
$selected_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$selected_time_frame = isset($_GET['time_frame']) ? $_GET['time_frame'] : '';
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
$selected_date = isset($_GET['order_date']) ? $_GET['order_date'] : '';

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

// Build the query with the filters
$query = "
SELECT orders.id, user_account.username, orders.order_date, orders.total_amount, orders.payment_method 
FROM orders
LEFT JOIN user_account ON orders.user_id = user_account.id
WHERE orders.status = 1
";

if (!empty($selected_method)) {
    $query .= " AND orders.payment_method = '" . mysqli_real_escape_string($conn, $selected_method) . "'";
}

if (!empty($selected_time_frame)) {
    $current_date = date('Y-m-d');
    switch ($selected_time_frame) {
        case 'last_7_days':
            $query .= " AND orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'last_30_days':
            $query .= " AND orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'this_month':
            $query .= " AND MONTH(orders.order_date) = MONTH(CURDATE()) AND YEAR(orders.order_date) = YEAR(CURDATE())";
            break;
    }
}

if (!empty($selected_date)) {
    $selected_date = date('Y-m-d', strtotime($selected_date));
    $query .= " AND DATE(orders.order_date) = '" . mysqli_real_escape_string($conn, $selected_date) . "'";
}

if (!empty($search_keyword)) {
    $query .= " AND (user_account.username LIKE '%" . mysqli_real_escape_string($conn, $search_keyword) . "%' 
                 OR orders.id LIKE '%" . mysqli_real_escape_string($conn, $search_keyword) . "%')";
}

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
?>
