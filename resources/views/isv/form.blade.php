<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Idade do Carro - ISV</title>
</head>

<body>
    <h1>Calculadora de Idade do Carro</h1>

    <form method="POST" action="{{ route('isv.calcular') }}">
        @csrf
        <label>Data da matrícula:</label>
        <input type="date" name="data_matricula" value="{{ old('data_matricula') }}" required>

        <br><br>

        <button type="submit">Calcular Faixa de Idade</button>
    </form>

    @if(isset($faixa))
    <h2>Resultado:</h2>
    <p>Data da matrícula: <strong>{{ $dataMatricula->format('d/m/Y') }}</strong></p>
    <p>Faixa de idade: <strong>{{ $faixa }}</strong></p>
    <p>Redução aplicável no ISV: <strong>{{ $reducao * 100 }}%</strong></p>
    @endif
</body>

</html>