<?php
error_reporting( E_ALL );
ini_set( "display_errors", 1 );
require __DIR__ . '/../vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

/* Fill in your own connector here */
$connector = new WindowsPrintConnector("POS");


/* Information for the receipt */
$items = array(
    new item("Example item #1", "4.00"),
    new item("Another thing", "3.50"),
    new item("Something else", "1.00"),
    new item("A final item", "4.45"),
);
$subtotal = new item('Subtotal', '12.95');

$taxable = new item('Taxable Amount', '12.95');
$tax = new item('Tax Amount', '12.95');
$billAmt = new item('Bill Amount', '12.95');
$paidAmt = new item('Paid Amount', '12.95');
$balAmt = new item('Bal. Amount', '12.95');



$tax = new item('A local tax', '1.30');
$nhil = new item('NHIL (2.5%)', '1.30');
$getf = new item('GETL (2.5%)', '1.30');
$covid = new item('COVID (1%)', '1.30');
$total = new item('TAXABLE AMT', '1.30');
$vat = new item('VAT (21.9%)', '1.30');
/* Date is kept the same for testing */
 $date = date('l jS \of F Y h:i:s A');
//$date = "Monday 6th of April 2015 02:56:25 PM";

/* Start the printer */
$logo = EscposImage::load("resources/escpos-php.png", false);
$printer = new Printer($connector);

/* Print top logo */
$printer -> setJustification(Printer::JUSTIFY_CENTER);
$printer -> graphics($logo);

/* Name of shop */
$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer -> text("ExampleMart Ltd.\n");
$printer -> selectPrintMode();
/* Title of receipt */
$printer -> setEmphasis(true);
$printer -> text("TAX INVOICE\n");
$printer -> setEmphasis(false);

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer -> text("Bill# 77");
$printer->feed();
$printer -> text("M# 77");
$printer ->feed();
$printer -> text("Admin");
$printer -> feed();



/* Items */
$printer -> setJustification(Printer::JUSTIFY_LEFT);
$printer -> setEmphasis(true);
$printer -> text(new item('', '$'));
$printer -> setEmphasis(false);
foreach ($items as $item) {
    $printer -> text($item);
}
//$printer -> setEmphasis(true);
//$printer -> text($subtotal);
//$printer -> setEmphasis(false);
$printer -> feed();

/* Tax and total */
$printer -> text($taxable);
$printer -> text($tax);
$printer -> text($billAmt);
$printer -> text($paidAmt);
$printer -> text($balAmt);
$printer -> feed();

$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer -> text("TAX BREAKDOWN\n");
$printer -> selectPrintMode();

$printer -> text($nhil);
$printer -> text($getf);
$printer -> text($covid);
$printer -> feed();
$printer->setUnderline(1);
//$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
//$printer -> setEmphasis(true);
//$printer -> text($total);
//$printer -> text($vat);
//$printer -> setEmphasis(false);
//$printer -> selectPrintMode();

/* Footer */
$printer -> feed(2);
$printer -> setJustification(Printer::JUSTIFY_CENTER);
$printer -> text("Thank you for shopping at ExampleMart\n");
$printer -> text("For trading hours, please visit example.com\n");
$printer -> feed(2);
$printer -> text($date . "\n");

/* Cut the receipt and open the cash drawer */
$printer -> cut();
$printer -> pulse();

$printer -> close();

/* A wrapper to do organise item names & prices into columns */
class item
{
    private $name;
    private $price;
    private $dollarSign;

    public function __construct($name = '', $price = '', $dollarSign = false)
    {
        $this -> name = $name;
        $this -> price = $price;
        $this -> dollarSign = $dollarSign;
    }
    
    public function __toString()
    {
        $rightCols = 10;
        $leftCols = 38;
        if ($this -> dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this -> name, $leftCols) ;
        
        $sign = ($this -> dollarSign ? '$ ' : '');
        $right = str_pad($sign . $this -> price, $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }
}
