<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración | PROXUS Tools</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: black;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr.details td {
            background-color: #f9f9f9;
            display: none;
            color: #333; /* Cambia el color del texto a un color oscuro */
        }
        .filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        select, button {
            padding: 10px;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .details div {
            padding: 10px;
            margin-top: 10px;
            background-color: #fff; /* Fondo blanco para el contenido */
            border: 1px solid #ddd; /* Borde para separar visualmente */
            color: #000; /* Color del texto */
        }
        .details ul {
            list-style-type: none;
            padding-left: 0;
            color: black;
        }
        .details ul li {
            margin-bottom: 5px;
        }
        .details ul li strong {
            display: block;
            margin-bottom: 5px;
        }
        .details-row {
            display: none;
        }
        .details-row td {
            padding: 5px;
            font-size: 14px;
            background-color: #f9f9f9;
            color: black;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once "includes/Aplicacion.php";

    if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'admin') {
        echo "<h1>Acceso denegado: se requiere acceso de administrador</h1>";
        exit;
    }

    $db = Aplicacion::getInstance()->getConnection();
    
    $gradoSeleccionado = isset($_GET['grado']) ? intval($_GET['grado']) : null;

    // Obtener lista de grados
    $queryGrados = "SELECT ID_grado, nombre FROM grados WHERE ID_universidad = 1";
    $stmt = $db->prepare($queryGrados);
    $stmt->execute();
    $grados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construir la consulta SQL
    $sql = "SELECT usuarios.ID_usuario, usuarios.nombre, usuarios.apellidos, usuarios.email, usuarios.user, grados.nombre as grado_nombre
            FROM usuarios
            LEFT JOIN grados ON usuarios.ID_grado = grados.ID_grado";
            
    if ($gradoSeleccionado) {
        $sql .= " WHERE usuarios.ID_grado = ?";
    }
    
    $stmt = $db->prepare($sql);
    if ($gradoSeleccionado) {
        $stmt->execute([$gradoSeleccionado]);
    } else {
        $stmt->execute();
    }

    // Contar usuarios
    $totalUsuarios = $stmt->rowCount();

    function loadUserTests($db, $userID) {
        $sql = "SELECT tests.titulo, respuesta_usuario.nota, respuesta_usuario.fecha
                FROM respuesta_usuario
                INNER JOIN tests ON respuesta_usuario.ID_test = tests.ID_test
                WHERE respuesta_usuario.ID_usuario = ?
                ORDER BY tests.titulo, respuesta_usuario.fecha";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userID]);

        $result = "";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result .= "<tr class='details-row' id='details-{$userID}'><td colspan='6'><strong>" . htmlspecialchars($row['titulo']) . "</strong> - Fecha: " . htmlspecialchars($row['fecha']) . " - Nota: " . htmlspecialchars($row['nota']) . "</td></tr>";
        }
        return $result;
    }
    ?>

    <?php include 'includes/comun/header.php'; ?>

    <div class="container">
        <h1>Panel de Administración</h1>
        
        <div class="filter">
            <form method="GET">
                <select name="grado">
                    <option value="">Todos los grados</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?= $grado['ID_grado'] ?>" <?= $gradoSeleccionado == $grado['ID_grado'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($grado['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtrar</button>
            </form>
        </div>
        
        <p>Total de usuarios: <?= $totalUsuarios ?></p>
        
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Usuario</th>
                    <th>Grado</th>
                    <th>Intentos</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['apellidos']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['user']) ?></td>
                        <td><?= htmlspecialchars($row['grado_nombre']) ?></td>
                        <td><button onclick="toggleDetails(this, <?= $row['ID_usuario'] ?>)">Mostrar</button></td>
                    </tr>
                    <tr class="details" id="details-<?= $row['ID_usuario'] ?>">
                        <td colspan="6">
                            <div>ID Usuario: <?= $row['ID_usuario'] ?></div>
                        </td>
                    </tr>
                    <?= loadUserTests($db, $row['ID_usuario']) ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include 'includes/comun/footer.php'; ?>

    <script>
        function toggleDetails(button, userID) {
            var details = document.getElementById('details-' + userID);
            var detailRows = document.querySelectorAll('#details-' + userID + ' + .details-row');
            if (details.style.display === 'none' || !details.style.display) {
                details.style.display = 'table-row';
                detailRows.forEach(function(row) {
                    row.style.display = 'table-row';
                });
                button.innerText = 'Ocultar';
            } else {
                details.style.display = 'none';
                detailRows.forEach(function(row) {
                    row.style.display = 'none';
                });
                button.innerText = 'Mostrar';
            }
        }
    </script>
</body>
</html>
