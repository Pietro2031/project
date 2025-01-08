<?php
require_once('tcpdf/tcpdf.php');
include('connection.php');

class MYPDF extends TCPDF {}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Inventory Report');
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Payment History', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');

$pdf->Ln(10);

// Get category and quantity filters from URL
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$quantityFilter = isset($_GET['quantity_filter']) ? $_GET['quantity_filter'] : 'all';

// Build query based on filters
$queries = [];
if ($category === 'all' || $category === 'base') {
    $queries[] = "SELECT 'Base' AS category, base_name AS item_name, quantity, price FROM coffee_base";
}
if ($category === 'all' || $category === 'flavors') {
    $queries[] = "SELECT 'Flavor' AS category, flavor_name AS item_name, quantity, price FROM coffee_flavors";
}
if ($category === 'all' || $category === 'toppings') {
    $queries[] = "SELECT 'Topping' AS category, topping_name AS item_name, quantity, price FROM coffee_toppings";
}

$query = implode(" UNION ", $queries);

if ($quantityFilter !== 'all') {
    $quantityCondition = '';
    if ($quantityFilter === 'low') {
        $quantityCondition = "quantity < 50";
    } elseif ($quantityFilter === 'medium') {
        $quantityCondition = "quantity BETWEEN 50 AND 200";
    } elseif ($quantityFilter === 'high') {
        $quantityCondition = "quantity > 200";
    }

    if ($category === 'all') {
        $query = "SELECT * FROM (" . $query . ") AS combined WHERE " . $quantityCondition;
    } else {
        $query .= " WHERE " . $quantityCondition;
    }
}

$result = $conn->query($query);

// Add table header
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(40, 8, 'Category', 1, 0, 'C');
$pdf->Cell(50, 8, 'Item Name', 1, 0, 'C');
$pdf->Cell(20, 8, 'Quantity', 1, 0, 'C');
$pdf->Cell(30, 8, 'Price', 1, 1, 'C');

$pdf->SetFont('helvetica', '', 10);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(40, 8, $row['category'], 1, 0, 'C');
        $pdf->Cell(50, 8, $row['item_name'], 1, 0, 'C');
        $pdf->Cell(20, 8, $row['quantity'], 1, 0, 'C');
        $pdf->Cell(30, 8, '₱' . number_format($row['price'], 2), 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 8, 'No items found.', 1, 1, 'C');
}

$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Report generated using Peter Beans System. All rights reserved.', 0, 1, 'C');


// Close and output PDF document
$pdf->Output('inventory_report.pdf', 'I');

$conn->close();
