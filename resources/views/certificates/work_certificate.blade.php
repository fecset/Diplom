<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Справка с места работы</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            margin: 40px auto;
            max-width: 700px;
            color: #000;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            margin: 0 5px;
        }
        .spacer {
            margin-top: 30px;
        }
        .small-text {
            font-size: 12px;
            color: #555;
            vertical-align: top;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        td {
            padding-top: 20px;
        }
        .signature-label {
            text-align: left;
        }
        .signature-line-cell {
             border-bottom: 1px solid #000;
             padding-top: 5px;
             text-align: center;
        }
    </style>
</head>
<body>

    <div class="right">
        <span class="underline" style="min-width: 300px;">АО "ЗТЗ"</span><br>
        <span class="small-text">(наименование организации)</span>
    </div>

     <div class="right spacer">
        <span class="underline" style="min-width: 300px;">г. Пересвет, ул. Бабушкина, д. 9</span><br>
        <span class="small-text">(адрес организации)</span>
    </div>

    <div class="right spacer">
        <span class="underline">{{ \Carbon\Carbon::now()->format('d.m.Y') }}</span><br>
        <span class="small-text">(дата выдачи)</span>
    </div>

    <div class="center spacer">
        <h2>СПРАВКА</h2>
    </div>

    <div class="spacer">
        <p>Выдана <span class="underline" style="min-width: 350px;">{{ $user->name }}</span> в том, что он(а) действительно работает в АО ЗТЗ с <span class="underline" style="min-width: 150px;">{{ $user->hired_at ? \Carbon\Carbon::parse($user->hired_at)->format('d.m.Y') : '[Дата найма]' }}</span> по настоящее время.</p>
        <p>Занимает должность: <span class="underline" style="min-width: 350px;">{{ $user->position?->name ?? '' }}</span>.</p>
        <p>Справка дана для предъявления по месту требования.</p>
    </div>

    <table>
        <tr>
            <td class="signature-label">Руководитель</td>
            <td style="width: 50%;"></td>
            <td class="signature-label">Подпись</td>
        </tr>
        <tr>
            <td>
                <div class="signature-line-cell"></div>
                <div class="small-text center" style="margin-top: 5px;">(ФИО)</div>
            </td>
            <td></td>
            <td>
                <div class="signature-line-cell"></div>
                <div class="small-text center" style="margin-top: 5px;">(расшифровка)</div>
            </td>
        </tr>
    </table>

</body>
</html> 