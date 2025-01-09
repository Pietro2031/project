<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');

$query = "SELECT userName, email, statuss, passwords, Addresss, ContactNum, profile_picture, Fname, Lname FROM user_account";
$result = mysqli_query($conn, $query);

class MYPDF extends TCPDF {}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your System Name');
$pdf->SetTitle('User Report');
$pdf->SetSubject('Generated Report');
$pdf->SetMargins(10, 30, 20);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(20);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'User Report', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 8, 'Username', 1, 0, 'C');
$pdf->Cell(30, 8, 'Email', 1, 0, 'C');
$pdf->Cell(25, 8, 'Status', 1, 0, 'C');
$pdf->Cell(30, 8, 'Address', 1, 0, 'C');
$pdf->Cell(25, 8, 'Contact', 1, 0, 'C');
$pdf->Cell(20, 8, 'First Name', 1, 0, 'C');
$pdf->Cell(20, 8, 'Last Name', 1, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
while ($row = mysqli_fetch_assoc($result)) {
    $statusText = $row['statuss'] == 'blocked' ? 'Blocked' : 'Active';

    $pdf->Cell(30, 8, $row['userName'], 1, 0, 'C');
    $pdf->Cell(30, 8, $row['email'], 1, 0, 'C');
    $pdf->Cell(25, 8, $statusText, 1, 0, 'C');
    $pdf->Cell(30, 8, $row['Addresss'], 1, 0, 'C');
    $pdf->Cell(25, 8, $row['ContactNum'], 1, 0, 'C');
    $pdf->Cell(20, 8, $row['Fname'], 1, 0, 'C');
    $pdf->Cell(20, 8, $row['Lname'], 1, 1, 'C');
}

$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Report generated using Peter Beans. All rights reserved.', 0, 1, 'C');

$pdf->Output('user_report.pdf', 'I'); // Output the PDF to the browser

mysqli_close($conn);
?>
