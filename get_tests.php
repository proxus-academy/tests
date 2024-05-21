<?php
require_once 'includes/Aplicacion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$db = Aplicacion::getInstance()->getConnection();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["ID_usuario"];

$check_admin_sql = "SELECT rol FROM usuarios WHERE ID_usuario = :userID";
$check_admin_stmt = $db->prepare($check_admin_sql);
$check_admin_stmt->bindValue(':userID', $user_id, PDO::PARAM_INT);
$check_admin_stmt->execute();
$is_admin = $check_admin_stmt->fetch(PDO::FETCH_ASSOC)['rol'] === 'admin';

if ($is_admin) {
    $sql = "SELECT tests.ID_test, tests.titulo, asignaturas.nombre AS asignatura_nombre, COUNT(respuesta_usuario.ID_intento) AS numero_intentos 
    FROM tests 
    INNER JOIN test_asignatura ON tests.ID_test = test_asignatura.ID_test 
    INNER JOIN asignaturas ON test_asignatura.ID_asignatura = asignaturas.ID_asignatura 
    LEFT JOIN respuesta_usuario ON tests.ID_test = respuesta_usuario.ID_test AND respuesta_usuario.ID_usuario = :userID
    GROUP BY tests.ID_test, asignaturas.nombre";
} else {
    $uni_grade_sql = "SELECT ID_universidad, ID_grado FROM usuarios WHERE ID_usuario = :userID";
    $uni_grade_stmt = $db->prepare($uni_grade_sql);
    $uni_grade_stmt->bindValue(':userID', $user_id, PDO::PARAM_INT);
    $uni_grade_stmt->execute();
    $user_details = $uni_grade_stmt->fetch(PDO::FETCH_ASSOC);
    $user_uni_id = $user_details['ID_universidad'];
    $user_grade_id = $user_details['ID_grado'];

    $sql = "SELECT tests.ID_test, tests.titulo, asignaturas.nombre AS asignatura_nombre, COUNT(respuesta_usuario.ID_intento) AS numero_intentos 
    FROM tests 
    INNER JOIN test_asignatura ON tests.ID_test = test_asignatura.ID_test 
    INNER JOIN asignaturas ON test_asignatura.ID_asignatura = asignaturas.ID_asignatura 
    INNER JOIN grado_asignatura ON asignaturas.ID_asignatura = grado_asignatura.ID_asignatura
    LEFT JOIN respuesta_usuario ON tests.ID_test = respuesta_usuario.ID_test AND respuesta_usuario.ID_usuario = :userID
    WHERE asignaturas.ID_universidad = :uniID AND grado_asignatura.ID_grado = :gradeID
    GROUP BY tests.ID_test, asignaturas.nombre";
}

$stmt = $db->prepare($sql);
$stmt->bindValue(':userID', $user_id, PDO::PARAM_INT);
if (!$is_admin) {
    $stmt->bindValue(':uniID', $user_uni_id, PDO::PARAM_INT);
    $stmt->bindValue(':gradeID', $user_grade_id, PDO::PARAM_INT);
}

if ($stmt->execute()) {
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $asignaturas = array_unique(array_column($tests, 'asignatura_nombre'));
} else {
    echo "Error: " . $stmt->errorInfo()[2];
}
?>
