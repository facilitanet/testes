<?php
ini_set("memory_limit","256M");
set_time_limit(0);

include "includes/conexao.php"; 
require_once("pdf/fpdf.php");
define('FPDF_FONTPATH','pdf/font/');
class PDF extends FPDF {
    function Header() {
        $this->Cell(190,9,"",0,1,'L');
    }
}

//$pdf=new PDF("P","mm","a4");
$pdf=new PDF("P","mm","Letter");
$pdf->AliasNbPages();
$pdf->setAuthor("RONALDO MARINS");
$pdf->setTitle("ETIQUETAS CAMPERJ");
$pdf->setCreator("PHP/FPDF");
$pdf->SetAutoPageBreak(true,8);
//$pdf->SetTopMargin("6");
$pdf->SetTopMargin("15");
$pdf->SetRightMargin("5");
$pdf->SetLeftMargin("5.3");
$pdf->Open();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

$sql = "
SELECT a.nome, a.endereco, a.bairro, a.cidade, a.estado, a.cep
FROM APP_CREDENCIADO a
GROUP BY a.nome, a.endereco, a.bairro, a.cidade, a.estado, a.cep";
$Resultador = mysql_query($sql) or die("Erro na Consulta: ".mysql_error());

while ($rs = mysql_fetch_array($Resultador)) {
        
        $endereco = $rs['endereco'];
        
        $bairro = ($rs['bairro'].' - '.$rs['cidade'].' / '.$rs['estado']);
        $cep = 'CEP: '.$rs['cep'].'                               R';
        $arrDados[] = array(
            "nome"      =>  utf8_decode($rs['nome']),
            "endereco"  =>  utf8_decode($endereco),
            "bairro"    =>  utf8_decode($bairro),
            "cep"       =>  $cep
        );
    }

$print_endereco = 1;
mysql_close();

$y=0;
for($x=0; $x < count($arrDados); $x++) {
    if (count($arrDados) >= $y+1) {
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(110,6,($arrDados[$y]?substr($arrDados[$y]['nome'],0,40):''),0,0,"L");
        $pdf->Cell(95,5.5,($arrDados[$y+1]?substr($arrDados[$y+1]['nome'],0,40):''),0,1,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(110,4,($print_endereco == 1 ? ($arrDados[$y]?$arrDados[$y]['endereco']:'') : ''),0,0,"L");
        $pdf->Cell(95,4,($print_endereco == 1 ? ($arrDados[$y+1]?$arrDados[$y+1]['endereco']:'') : ''),0,1,"L");
        $pdf->Cell(110,4,($print_endereco == 1 ? ($arrDados[$y]?$arrDados[$y]['bairro']:'') : ''),0,0,"L");
        $pdf->Cell(95,4,($print_endereco == 1 ? ($arrDados[$y+1]?$arrDados[$y+1]['bairro']:'') : ''),0,1,"L");
        $pdf->Cell(110,4,($print_endereco == 1 ? ($arrDados[$y]?$arrDados[$y]['cep']:'') : ''),0,0,"L");
        $pdf->Cell(95,4,($print_endereco == 1 ? ($arrDados[$y+1]?$arrDados[$y+1]['cep']:'') : ''),0,1,"L");
        $retMod = (($x+1) % 7);
        if ($retMod==0) {   $pdf->AddPage();    }
        else            {   $pdf->Ln(17);        }
    }
    $y += 2;
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: public"); // HTTP/1.0
$pdf->Output();