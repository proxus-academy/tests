<!DOCTYPE html>
<html lang="es">
<head>
    <title>PROXUS | Test</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.4.0/styles/default.min.css">
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.4.0/highlight.min.js"></script>
    <script>
        hljs.highlightAll();
    </script>
    <style>
        pre, pre code {
            border-radius: 8px; /* Ajusta este valor según lo redondeado que desees que sean las esquinas */
        }

        #timer-container {
            position: fixed;
            top: 0px;
            right: 0px;
            background-color: rgba(255, 255, 255, 0.8); /* Fondo medio transparente */
            border-radius: 0 0 0 20px; /* Esquina inferior izquierda redonda */
            padding: 15px 25px;
            width: 80px;
            z-index: 1000;
        }

        #timer {
            font-size: 30px;
            color: black;
        }

    </style>


    <?php
        session_start();
        
        require_once 'includes/Aplicacion.php';
        $db = Aplicacion::getInstance()->getConnection(); // Obtener la conexión usando el patrón Singleton
        
        $test_id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($test_id === null) {
            exit('El ID del test no está definido.');
        }
        
        // Preparar y ejecutar la consulta para obtener el título del test
        $stmtTitulo = $db->prepare("SELECT titulo FROM tests WHERE ID_test = ?");
        $stmtTitulo->bindValue(1, $test_id, PDO::PARAM_INT);
        $stmtTitulo->execute();
        $tituloTest = $stmtTitulo->rowCount() > 0 ? $stmtTitulo->fetch(PDO::FETCH_ASSOC)['titulo'] : "Título no encontrado";

        // Preparar y ejecutar la consulta para obtener el nombre de la asignatura
        $stmtAsignatura = $db->prepare("SELECT asignaturas.nombre FROM asignaturas INNER JOIN test_asignatura ON asignaturas.ID_asignatura = test_asignatura.ID_asignatura WHERE test_asignatura.ID_test = ?");
        $stmtAsignatura->bindValue(1, $test_id, PDO::PARAM_INT);
        $stmtAsignatura->execute();
        $nombreAsignatura = $stmtAsignatura->rowCount() > 0 ? $stmtAsignatura->fetch(PDO::FETCH_ASSOC)['nombre'] : "Asignatura no encontrada";

        // Preparar y ejecutar la consulta para obtener las preguntas
        $stmtPreguntas = $db->prepare("SELECT preguntas.ID_pregunta, preguntas.pregunta FROM preguntas WHERE preguntas.ID_test = ?");
        $stmtPreguntas->bindValue(1, $test_id, PDO::PARAM_INT);
        $stmtPreguntas->execute();
        $preguntas = $stmtPreguntas->fetchAll(PDO::FETCH_ASSOC);

        // Selecciona aleatoriamente 10 preguntas si hay suficientes, de lo contrario selecciona todas
        $numPreguntas = count($preguntas);
        $preguntasAleatorias = ($numPreguntas > 20) ? array_rand($preguntas, 20) : array_keys($preguntas);
        
        // Crear un nuevo array con las preguntas seleccionadas
        $preguntasSeleccionadas = array_intersect_key($preguntas, array_flip($preguntasAleatorias));

        // Mezclar el array de preguntas seleccionadas
        shuffle($preguntasSeleccionadas);

        $_SESSION['preguntas_test'] = $preguntasSeleccionadas; // Guardar las preguntas embarajadas en sesión

        function highlightCode($text) {
            $codePattern = '/\[code\](.*?)\[\/code\]/s'; // Regex para encontrar texto entre marcadores [code]
            return preg_replace_callback($codePattern, function($matches) {
                return "<pre><code class='cpp'>" . htmlspecialchars($matches[1]) . "</code></pre>"; // Utiliza highlight.js
            }, $text);
        }
        
        
    ?>
</head>
<body>
    

    <?php include 'includes/comun/header.php'; ?>
   

    <div class="container">
    <div id="timer-container">
        <div id="timer">60:00</div>
    </div>
        <h2><?php echo htmlspecialchars($tituloTest); ?></h2>
        
        <form action="logic/procesar_test.php" method="post" style="margin-top: 30px;">
            <input type="hidden" name="total_preguntas" value="<?php echo count($preguntasSeleccionadas); ?>">
            <input type="hidden" name="ID_test" value="<?php echo $test_id; ?>">
            <?php $questionNumber = 1; ?>
            <?php foreach ($preguntasSeleccionadas as $row): ?>
                <div class="question-card">
                    <div class="question-header">
                        <div class="question-number"><?php echo $questionNumber++; ?></div>
                        <div class="question-text">
                            <?php 
                            $questionText = highlightCode($row['pregunta']);
                            echo $questionText; 
                            ?>
                        </div>
                    </div>
                    <?php
                        $pregunta_id = $row['ID_pregunta'];
                        $stmtOpciones = $db->prepare("SELECT ID_opcion, opcion FROM opciones WHERE opciones.ID_test = ? AND opciones.ID_pregunta = ?");
                        $stmtOpciones->bindValue(1, $test_id, PDO::PARAM_INT);
                        $stmtOpciones->bindValue(2, $pregunta_id, PDO::PARAM_INT);
                        $stmtOpciones->execute();
                        $opciones = $stmtOpciones->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="question-options">
                        <?php foreach ($opciones as $row2): ?>
                            <div class="question-option">
                            <label>
                                <input type='radio' name='pregunta_<?php echo $pregunta_id; ?>' value='<?php echo $row2['ID_opcion']; ?>' onclick='toggleRadio(this);' waschecked='false'>
                                <?php
                                $optionText = highlightCode($row2['opcion']);
                                echo $optionText;
                                ?>
                            </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="button" onclick="mostrarConfirmacion()" class="submit-button">Enviar Test</button>
        </form>

        <div id="popup-overlay" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <h2>Tiempo agotado</h2>
                <p>El tiempo para completar el test ha terminado.</p>
                <button onclick="verResultados()" class="btn-primary">Ver resultados</button>
            </div>
        </div>

        <!-- Pop-up de Confirmación Envío Test -->
        <div id="sendTestPopUp" class="popup-overlay" style="display:none;">
            <div class="popup-content">
                <h2>¿Seguro que ya lo has terminado?</h2>
                <button onclick="closePopup()" class="btn-default">Cancelar</button>
                <button onclick="enviarTest()" class="btn-primary" style="text-decoration: none;">Enviar</button>
            </div>
        </div>


    </div>

    <script>
        // Define el tiempo inicial en segundos (60 minutos)
        var tiempoInicial = 60 * 60;
        var tiempoRestante = tiempoInicial;

        // Actualiza el temporizador cada segundo
        var intervalId = setInterval(actualizarTemporizador, 1000);

        function actualizarTemporizador() {
            // Disminuye el tiempo restante
            tiempoRestante--;

            // Si el tiempo restante llega a cero, muestra el pop-up y detiene el temporizador
            if (tiempoRestante <= 0) {
                clearInterval(intervalId); // Detiene el temporizador

                // Muestra el pop-up
                mostrarPopup();
            }

            // Calcula los minutos y segundos restantes
            var minutos = Math.floor(tiempoRestante / 60);
            var segundos = tiempoRestante % 60;

            // Formatea el tiempo en formato "mm:ss"
            var tiempoFormateado = (minutos < 10 ? "0" : "") + minutos + ":" + (segundos < 10 ? "0" : "") + segundos;

            // Actualiza el contenido del temporizador
            document.getElementById("timer").innerText = tiempoFormateado;
        }

        function mostrarPopup() {
            // Muestra el overlay del pop-up
            document.getElementById("popup-overlay").style.display = "flex";
        }

        function ocultarPopup() {
            // Oculta el overlay del pop-up
            document.getElementById("popup-overlay").style.display = "none";
        }

        function verResultados() {
            // Envía el formulario
            document.querySelector("form").submit();
        }

        // Agrega un evento para detectar clics en enlaces
        document.addEventListener("click", function(event) {
            // Añade una comprobación para excluir el botón de subir al principio de la página
            if (event.target.tagName === "A" && event.target.id !== "scrollToTopButton") {
                // Pregunta al usuario si desea realizar la acción
                var reiniciar = confirm("¿Deseas reiniciar el test desde cero?");
                if (reiniciar) {
                    // Redirige al usuario al destino del enlace si es necesario
                    var href = event.target.getAttribute("href");
                    if (href) {
                        window.location.href = href;
                    }
                } else {
                    // Cancela la navegación predeterminada si el usuario no desea reiniciar el test
                    event.preventDefault();
                }
            }
        });

        function mostrarConfirmacion() {
            document.getElementById("sendTestPopUp").style.display = "flex";
        }

        function closePopup() {
            document.getElementById("sendTestPopUp").style.display = "none";
        }

        function enviarTest() {
            document.querySelector("form").submit();
        }

        // permite desseleccionar las opciones una vez marcadas
        document.addEventListener('DOMContentLoaded', function() {
            // Selecciona todos los input de tipo radio en el formulario
            const radios = document.querySelectorAll('input[type="radio"]');

            radios.forEach(radio => {
                radio.addEventListener('click', function() {
                    // Verifica si el radio ya estaba marcado
                    if (this.getAttribute('waschecked') === 'true') {
                        this.checked = false;
                        this.setAttribute('waschecked', 'false');
                    } else {
                        // Deselecciona cualquier otro radio marcado en el mismo grupo
                        radios.forEach(el => {
                            if (el !== this && el.name === this.name) {
                                el.setAttribute('waschecked', 'false');
                            }
                        });
                        
                        this.setAttribute('waschecked', 'true');
                    }
                });
            });
        });
    </script>

    <?php include 'includes/comun/footer.php'; ?>
</body>
</html>