<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificado - <?php echo htmlspecialchars($curso['titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #525659;
            font-family: sans-serif;
        }

        .view-container {
            padding: 20px 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Tamaño Carta Horizontal (Landscape) */
        .certificado-page {
            background: white;
            width: 11in;
            height: 8.5in;
            margin: 0 auto;
            padding: 0.2in;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .border-main {
            border: 10px solid #065b3e;
            width: 100%;
            height: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .border-inner {
            border: 2px solid #bfa071;
            width: 100%;
            height: 100%;
            padding: 30px;
            text-align: center;
            background-color: #fdfdfd;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Logos redimensionados */
        .cert-logo {
            width: 100px;
            margin-bottom: 5px;
        }

        .uabc-escudo {
            width: 80px;
            margin-bottom: 10px;
        }

        .cert-fca {
            font-size: 0.8rem;
            color: #444;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .cert-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #065b3e;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .cert-text {
            font-size: 0.95rem;
            color: #333;
            margin-bottom: 0;
        }

        .cert-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: #a67c00;
            font-weight: bold;
            margin: 10px 0;
            border-bottom: 2px solid #a67c00;
            display: inline-block;
            padding: 0 30px;
        }

        .cert-course {
            font-size: 1.4rem;
            color: #065b3e;
            font-weight: bold;
            display: block;
            margin-top: 5px;
        }

        .disclaimer-box {
            margin-top: 25px;
            padding: 10px;
            border: 1px dashed #bfa071;
            background: rgba(229, 169, 59, 0.05);
            max-width: 750px;
        }

        .disclaimer-text {
            font-size: 0.7rem;
            color: #666;
            font-style: italic;
            margin: 0;
        }

        .btn-uabc-gold {
            background-color: #e5a93b;
            color: #111;
            font-weight: bold;
        }

        .no-print-zone {
            text-align: center;
            margin-bottom: 15px;
        }

        @media print {

            /* 1. Ocultar todo lo que no sea el certificado */
            body * {
                visibility: hidden;
            }

            /* 2. Solo mostrar el contenedor del certificado y su contenido */
            .certificado-page,
            .certificado-page * {
                visibility: visible;
            }

            /* 3. Posicionar el certificado en la esquina superior izquierda de la hoja */
            .certificado-page {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                background: white !important;
            }

            /* 4. Forzar configuración de página */
            @page {
                size: letter landscape;
                margin: 0mm;
                /* Elimina márgenes blancos de la impresora */
            }

            /* 5. Asegurar que los bordes y colores se impriman (opcional, activa fondos en Chrome) */
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background: white !important;
            }
        }
    </style>
</head>

<body>

    <div class="view-container">
        <div class="no-print-zone">
            <a href="index.php?c=estudiante&a=miProgreso" class="btn btn-outline-light me-2 shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i> Volver
            </a>
            <button onclick="window.print()" class="btn btn-uabc-gold shadow px-4">
                <i class="fa-solid fa-print me-2"></i> Imprimir / Guardar PDF
            </button>
        </div>

        <div class="certificado-page">
            <div class="border-main">
                <div class="border-inner">
                    <div class="d-flex gap-4">
                        <img src="Assets\Img\Logo CNI.png" alt="CNI" class="cert-logo">
                        <img src="Assets/Img/EscudoUABC.png" alt="UABC" class="uabc-escudo">
                    </div>

                    <div class="cert-fca">Centro de Negocios e Incubadora - UABC</div>
                    <h1 class="cert-title">Reconocimiento</h1>

                    <p class="cert-text">Se otorga el presente documento a:</p>
                    <div class="cert-name"><?php echo mb_strtoupper($_SESSION['nombre'], 'UTF-8'); ?></div>

                    <p class="cert-text">Por su dedicación y cumplimiento en el curso:</p>
                    <span class="cert-course">"<?php echo mb_strtoupper($curso['titulo'], 'UTF-8'); ?>"</span>

                    <div class="cert-footer mt-4">
                        <p class="cert-text">Expedido el: <b><?php echo date('d / m / Y'); ?></b></p>

                        <div class="disclaimer-box">
                            <p class="disclaimer-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Aviso:</strong> Documento con fines académicos y de carácter simbólico. No cuenta con validez oficial ante instancias externas o administrativas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>