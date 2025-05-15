<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y es superusuario o administrador
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] != 'superusuario' && $_SESSION['user_role'] != 'administrador')) {
    // Redirigir a la página de inicio de sesión
    header("Location: ../index.html");
    exit;
}

// Incluir archivo de conexión
require_once '../config/database.php';

// Incluir librería TCPDF
require_once '../vendor/tcpdf/tcpdf.php';

// Si la librería TCPDF no está instalada, mostrar mensaje de error
if (!class_exists('TCPDF')) {
    echo "Error: La librería TCPDF no está instalada. Por favor, instale TCPDF para generar PDFs.";
    echo "<br><br><a href='../admin/medicamentos.php'>Volver a Medicamentos</a>";
    exit;
}

try {
    // Obtener todos los medicamentos
    $stmt = $conn->query("SELECT * FROM medicamentos ORDER BY nombre ASC");
    $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear nuevo documento PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Establecer información del documento
    $pdf->SetCreator('FarmaExpress');
    $pdf->SetAuthor('Administrador');
    $pdf->SetTitle('Listado de Medicamentos');
    $pdf->SetSubject('Medicamentos FarmaExpress');
    
    // Establecer márgenes
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    // Eliminar cabecera y pie de página predeterminados
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Establecer fuente predeterminada
    $pdf->SetFont('helvetica', '', 10);
    
    // Añadir página
    $pdf->AddPage();
    
    // Cabecera del documento
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'FarmaExpress - Listado de Medicamentos', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Fecha de exportación: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Cabecera de la tabla
    $pdf->SetFillColor(13, 148, 136);
    $pdf->SetTextColor(255);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(10, 7, 'ID', 1, 0, 'C', 1);
    $pdf->Cell(50, 7, 'Nombre', 1, 0, 'C', 1);
    $pdf->Cell(30, 7, 'Categoría', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'Precio', 1, 0, 'C', 1);
    $pdf->Cell(15, 7, 'Stock', 1, 0, 'C', 1);
    $pdf->Cell(50, 7, 'Laboratorio', 1, 1, 'C', 1);
    
    // Datos de la tabla
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetTextColor(0);
    $pdf->SetFont('helvetica', '', 9);
    
    $fill = false;
    foreach ($medicamentos as $med) {
        $pdf->Cell(10, 6, $med['id'], 1, 0, 'C', $fill);
        $pdf->Cell(50, 6, $med['nombre'], 1, 0, 'L', $fill);
        $pdf->Cell(30, 6, $med['categoria'], 1, 0, 'L', $fill);
        $pdf->Cell(20, 6, '$' . number_format($med['precio'], 2), 1, 0, 'R', $fill);
        
        // Color para el stock
        if ($med['stock'] < 10) {
            $pdf->SetTextColor(185, 28, 28); // Rojo
        } elseif ($med['stock'] < 30) {
            $pdf->SetTextColor(146, 64, 14); // Naranja
        } else {
            $pdf->SetTextColor(4, 120, 87); // Verde
        }
        
        $pdf->Cell(15, 6, $med['stock'], 1, 0, 'C', $fill);
        $pdf->SetTextColor(0); // Restaurar color
        $pdf->Cell(50, 6, $med['laboratorio'], 1, 1, 'L', $fill);
        
        $fill = !$fill; // Alternar colores
    }
    
    $pdf->Ln(10);
    
    // Añadir página para detalles de medicamentos con imágenes
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Detalles de Medicamentos', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Mostrar detalles de cada medicamento con imagen
    foreach ($medicamentos as $med) {
        // $pdf->SetFont('helvetica', 'B', 12);
        // $pdf->Cell(0, 10, $med['nombre'], 0, 1, 'L');
        
        // // Crear una tabla para la información y la imagen
        // $pdf->SetFont('helvetica', '', 10);
        
        // // Intentar añadir la imagen
        // $imagePath = '../' . $med['imagen'];
        // $hasImage = file_exists($imagePath) && is_file($imagePath);
        
        // if ($hasImage) {
        //     // Crear una celda con la imagen a la izquierda
        //     $startY = $pdf->GetY();
        //     $pdf->Image($imagePath, 15, $startY, 30, 30, '', '', '', true, 300, '', false, false, 1);
        //     $pdf->SetXY(50, $startY);
        // }
        
        // Información del medicamento
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 6, 'Categoría:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $med['categoria'], 0, 1, 'L');
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 6, 'Precio:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, '$' . number_format($med['precio'], 2), 0, 1, 'L');
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 6, 'Stock:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $med['stock'] . ' unidades', 0, 1, 'L');
        
        if (!empty($med['laboratorio'])) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(40, 6, 'Laboratorio:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, $med['laboratorio'], 0, 1, 'L');
        }
        
        // Descripción
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'Descripción:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 6, $med['descripcion'], 0, 'L');
        
        $pdf->Ln(5);
        $pdf->Cell(0, 0, '', 'T', 1); // Línea separadora
        $pdf->Ln(5);
        
        // Verificar si necesitamos una nueva página
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
        }
    }
    
    // Generar el PDF
    $pdfFileName = 'medicamentos_' . date('Ymd_His') . '.pdf';
    $pdf->Output($pdfFileName, 'D'); // 'D' para descargar
    
} catch(PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
    echo "<br><br><a href='../admin/medicamentos.php'>Volver a Medicamentos</a>";
}
?>
