<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$html = '<html><body>
<table border="1">
<tr>
<td style="width:30px; height: 120px; text-align:center; vertical-align:middle;">
<div style="display:block; width:100px; transform: rotate(-90deg); transform-origin: center center; font-family:Arial; font-weight:bold; font-size:12px;">ABSENCES</div>
</td>
<td>Other Content<br>Line 2<br>Line 3</td>
</tr>
</table>
</body></html>';
$dompdf->loadHtml($html);
$dompdf->render();
file_put_contents('test_div.pdf', $dompdf->output());
echo "PDF DIV generated\n";
