{{-- CSS partilhado pelos contratos em PDF (cotação convertida e angariador). --}}
<style>
        @page {
            margin: 90px 55px 70px 55px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            line-height: 1.55;
            color: #222;
        }

        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 1px solid #b08d3c;
        }

        header img {
            height: 52px;
        }

        header .ref {
            position: absolute;
            right: 0;
            top: 4px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }

        footer {
            position: fixed;
            bottom: -45px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .pagenum:before {
            content: counter(page);
        }

        h1 {
            text-align: center;
            font-size: 16px;
            letter-spacing: 1px;
            margin: 0 0 4px 0;
        }

        .subtitulo {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 18px;
        }

        h2 {
            font-size: 11.5px;
            color: #7a5c14;
            border-bottom: 1px solid #e3d5b0;
            padding-bottom: 3px;
            margin: 16px 0 6px 0;
            page-break-after: avoid;
        }

        p {
            margin: 4px 0;
            text-align: justify;
        }

        ul,
        ol {
            margin: 4px 0 4px 0;
            padding-left: 16px;
        }

        li {
            margin-bottom: 2px;
            text-align: justify;
        }

        .partes td {
            vertical-align: top;
            width: 50%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            background: #fafafa;
        }

        .partes .titulo {
            font-weight: bold;
            color: #7a5c14;
            text-transform: uppercase;
            font-size: 9.5px;
            margin-bottom: 4px;
        }

        .prazos {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .prazos td {
            border: 1px solid #ddd;
            padding: 5px 8px;
        }

        .prazos td.fase {
            width: 55%;
        }

        .prazos td.prazo {
            font-weight: bold;
            white-space: nowrap;
        }

        .destaque {
            border-left: 3px solid #b08d3c;
            background: #fbf6e9;
            padding: 6px 10px;
            margin: 8px 0;
            page-break-inside: avoid;
        }

        .pagamento {
            border: 1px solid #ddd;
            padding: 6px 10px;
            margin: 6px 0;
            page-break-inside: avoid;
        }

        .cotacao {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            page-break-inside: avoid;
        }

        .cotacao td,
        .cotacao th {
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: left;
        }

        .cotacao th {
            background: #fbf6e9;
            color: #7a5c14;
            font-size: 9.5px;
            text-transform: uppercase;
        }

        .cotacao td.valor {
            width: 25%;
            text-align: right;
            white-space: nowrap;
        }

        .cotacao tr.total td {
            background: #f3ead2;
            font-weight: bold;
        }

        .cotacao tr.info td {
            color: #555;
            background: #fafafa;
        }

        .cotacao .nota {
            display: block;
            font-size: 8.5px;
            font-weight: normal;
            color: #777;
        }

        .clausula {
            page-break-inside: avoid;
        }

        .assinaturas {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }

        .assinaturas td {
            text-align: center;
            vertical-align: bottom;
            padding: 10px 20px;
            width: 50%;
        }
</style>
