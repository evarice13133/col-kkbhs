<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$html = '<html><body>
<table border="1">
<tr>
<td style="width:30px; height: 100px; text-align:center; vertical-align:middle;">
<svg width="20" height="80">
<text x="-80" y="15" transform="rotate(-90)" font-family="Arial" font-weight="bold" font-size="12" fill="#000">ABSENCES</text>
</svg>
</td>
<td>Other Content<br>Line 2<br>Line 3</td>
</tr>
</table>
</body></html>';
$dompdf->loadHtml($html);
$dompdf->render();
file_put_contents('test_svg.pdf', $dompdf->output());
echo "PDF generated\n";
