<?php include 'includes/comun/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description">
    <title>Tests | PROXUS Tools</title>
    <style>
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2em;
        }

        .form-container select {
            padding: 0.5em;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 1em;
        }

        .form-container button {
            padding: 0.5em 1em;
            font-size: 1em;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-container button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function filterTests() {
            const asignatura = document.getElementById('asignatura').value;
            const cards = document.getElementsByClassName('card');
            
            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                if (asignatura === 'all' || card.dataset.asignatura === asignatura) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        }
    </script>
</head>
<body>
    <?php include 'includes/comun/header.php'; ?>
    <?php include 'logic/get_tests.php'; ?>

    <div class="container">
        <h2>Tests Disponibles</h2>
        <div class="separator-gray"></div>
        <div class="form-container" style="margin-bottom: -320px;">
            <label for="asignatura">Filtrar por asignatura:</label>
            <select id="asignatura" onchange="filterTests()">
                <option value="all">Todas las asignaturas</option>
                <?php foreach ($asignaturas as $asignatura): ?>
                    <option value="<?php echo htmlspecialchars($asignatura); ?>"><?php echo htmlspecialchars($asignatura); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($tests)): ?>
            <?php foreach ($tests as $row): ?>
                <div class="card" data-asignatura="<?php echo htmlspecialchars($row["asignatura_nombre"]); ?>">
                    <div class="card2">
                        <h3><?php echo htmlspecialchars($row["titulo"]); ?></h3>
                        <p>Asignatura: <?php echo htmlspecialchars($row["asignatura_nombre"]); ?></p>
                        <p>Intentos realizados: <?php echo htmlspecialchars($row["numero_intentos"]); ?></p>
                        <?php if ($row["titulo"] == "Simulacro Examen | FAL"): ?>
                            <a href="realize_test_simulacro_fal.php?id=<?php echo htmlspecialchars($row['ID_test']); ?>" class="buttonT">Realizar Test (10 preg, 20mins, -1/3)</a>
                            <?php elseif($row["titulo"] == "Simulacro Examen | AI2"): ?>
                            <a href="realize_test_simulacro_ai2.php?id=<?php echo htmlspecialchars($row['ID_test']); ?>" class="buttonT">Realizar Test (20 preg, 50mins, -1/3)</a>
                            <?php elseif($row["titulo"] == "Simulacro Examen | IC"): ?>
                            <a href="realize_test_simulacro_ic.php?id=<?php echo htmlspecialchars($row['ID_test']); ?>" class="buttonT">Realizar Test (40 preg, 45mins, -1/2)</a>
                            <?php elseif($row["titulo"] == "Simulacro Examen | RRHH"): ?>
                                <a href="realize_test_simulacro_fal.php?id=<?php echo htmlspecialchars($row['ID_test']); ?>" class="buttonT">Realizar Test (10 preg, 20mins, -1/3)</a>
                            <?php else: ?>
                            <a href="realize_test.php?id=<?php echo htmlspecialchars($row['ID_test']); ?>" class="buttonT">Realizar Test</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; margin-top: 40px;">No hay tests disponibles para este usuario.</p>
        <?php endif; ?>
    </div>

    <?php include 'includes/comun/footer.php'; ?>
</body>
</html>
