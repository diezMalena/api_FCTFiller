<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cuestionario FCT {{ $titulo }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-3">ID Usuario: {{ $id_usuario }}</h2>
        <h3 class="text-center mb-3">Título: {{ $titulo }}</h3>
        <h3 class="text-center mb-3">Rol usuario: {{ $destinatario }}</h3>
        <h3 class="text-center mb-3">Código centro: {{ $codigo_centro }}</h3>
        <h3 class="text-center mb-3">Curso académico: {{ $curso_academico }}</h3>
        <h3 class="text-center mb-3">Ciclo: {{ $ciclo }}</h3>
        <table class="table table-bordered mb-5">
            <thead>
                <tr class="table-danger">
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
