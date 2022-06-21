<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cuestionario FCT {{ $titulo }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        .resumen-cuestionario-th{
            border:1px solid #ececec;
            padding:0 20px;
            background-color:#225082;
            color:#ececec;
        }
        .resumen-cuestionario-td{
            border:1px solid #ececec;
            padding:0 20px;
            background-color: #5f7ea0;
            color:#ececec;
        }
        .resumen-cuestionario-table{
            margin: 20px auto;
            padding: 20px auto;
            width: 90%;
            border:1px solid #225082;
        }
        .container-table{
            background-color: #ececec;
            width: 100%;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .table-cuestionario th{
            background-color: #225082;
            color:#ececec;
            border:1px #ececec;
        }

    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Cuestionario FCT</h2>
        <div class="container-table">

            <table class="resumen-cuestionario-table">

                <tr class="resumen-cuestionario-tr">
                    <th class="resumen-cuestionario-th">ID usuario</th>
                    <td class="resumen-cuestionario-td">{{ $id_usuario }}</td>
                </tr>

                <tr>
                    <th class="resumen-cuestionario-th"">Título</th>
                    <td class="resumen-cuestionario-td">{{ $titulo }}</td>
                </tr>

                <tr>
                    <th class="resumen-cuestionario-th">Destinatario</th>
                    <td class="resumen-cuestionario-td">{{ $destinatario }}</td>
                </tr>

                <tr>
                    <th class="resumen-cuestionario-th">Código Centro</th>
                    <td class="resumen-cuestionario-td">{{ $codigo_centro }}</td>
                </tr>

                <tr>
                    <th class="resumen-cuestionario-th">Curso Académico</th>
                    <td class="resumen-cuestionario-td">{{ $curso_academico }}</td>
                </tr>

                <tr>
                    <th class="resumen-cuestionario-th">Ciclo</th>
                    <td class="resumen-cuestionario-td">{{ $ciclo }}</td>
                </tr>

            </table>

        </div>

        <hr>


        <table class="table table-bordered mb-5 mt-5">
            <thead>
                <tr class="table-cuestionario">
                    <th scope="col">Pregunta</th>
                    <th scope="col">Respuesta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos ?? '' as $data)
                <tr>
                    <td>{{ $data->pregunta }}</td>
                    <td>{{ $data->respuesta }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
