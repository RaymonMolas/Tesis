<?php
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
}
$tipoUsuario = $_SESSION["tipo_usuario"] ?? "";
$esCliente = $tipoUsuario === "cliente";
$esAdmin = $tipoUsuario === "personal" || $tipoUsuario === "administrador";
$id_cliente = $_SESSION["id_cliente"] ?? null;
$citas = $esAdmin ? ControladorAgendamiento::obtenerCitas() : [];
$tieneCitaActiva = false;
if ($esCliente && $id_cliente !== null) {
    $tieneCitaActiva = ModeloAgendamiento::clienteTieneCitaActiva($id_cliente);
}
$tieneCitaActivaJS = $tieneCitaActiva ? 'true' : 'false';
$esClienteJS = $esCliente ? 'true' : 'false';
$citasJSON = json_encode($citas);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["fecha"], $_POST["hora"], $_POST["motivo"])) {
    $_POST["id_cliente"] = $_SESSION["id_cliente"] ?? null;

    if ($_POST["id_cliente"] && !ModeloAgendamiento::clienteTieneCitaActiva($_POST["id_cliente"])) {
        $resultado = ControladorAgendamiento::guardarCita();
        $_SESSION["mensajeJS"] = $resultado === "ok"
            ? "Swal.fire('Cita solicitada', 'Tu cita fue registrada correctamente.', 'success');"
            : "Swal.fire('Error', 'Ocurrió un error al registrar la cita.', 'error');";
    } else {
        $_SESSION["mensajeJS"] = "Swal.fire('No permitido', 'Ya tienes una cita activa.', 'warning');";
    }
    echo '<script>window.location="index.php?pagina=agendamiento";</script>';
    exit;
}
?>

<title>Agendamiento</title>
<style>
    .calendar-container {
        max-width: 1200px;
        margin: 30px auto;
        font-family: "Segoe UI", Tahoma, sans-serif;
        background-color: #f9fafb;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #ffffff;
        padding: 20px 30px;
        border-bottom: 1px solid #e0e0e0;
    }

    .calendar-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }

    .calendar-header button {
        background-color: #f2f2f2;
        border: none;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .calendar-header button:hover {
        background-color: #e0e0e0;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background-color: #f4f6f8;
        color: #5f6368;
        font-weight: 600;
        text-align: center;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background-color: #fff;
    }

    .calendar-grid div {
        padding: 6px;
        border: 1px solid #f0f0f0;
        min-height: 100px;
        box-sizing: border-box;
        overflow-y: auto;
        position: relative;
        cursor: pointer;
    }

    .calendar-grid div:hover {
        background-color: #f0f0f0;
    }

    .calendar-grid div strong {
        display: block;
        margin-bottom: 4px;
        font-weight: 600;
        font-size: 13px;
        color: #333;
    }

    .calendar-grid .contador-citas {
        font-size: 13px;
        color: white;
        background-color: #dc3545;
        min-height: 10px;
        padding: 2px 6px;
        border-radius: 5px;
        display: inline-block;
        margin-top: 4px;
    }

    .current-day {
        border: 2px solid #1a73e8;
        background-color: lightblue;
    }
</style>

<div class="calendar-container">
    <div class="calendar-header">
        <button onclick="prevMonth()">&larr;</button>
        <h2 id="calendar-title"></h2>
        <button onclick="nextMonth()">&rarr;</button>
    </div>
    <div class="calendar-days">
        <div>Dom</div>
        <div>Lun</div>
        <div>Mar</div>
        <div>Mié</div>
        <div>Jue</div>
        <div>Vie</div>
        <div>Sáb</div>
    </div>
    <div id="gridcalendar" class="calendar-grid"></div>
</div>

<!-- Modal Detalles del Día -->
<div class="modal fade" id="modalCitasDelDia" tabindex="-1" aria-labelledby="modalCitasDelDiaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCitasDelDiaLabel">Citas del Día</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="contenidoCitasDia"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal para Agendar Cita (Cliente) -->
<div class="modal fade" id="modalAgendarCita" tabindex="-1" aria-labelledby="modalAgendarCitaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgendarCitaLabel">Agendar Nueva Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_cliente" value="<?php echo $id_cliente ?? ''; ?>">
                <input type="hidden" name="fecha" id="inputFechaCita">

                <div class="mb-3">
                    <label for="hora" class="form-label">Hora</label>
                    <input type="time" name="hora" class="form-control" required min="07:00" max="18:00">
                    <small class="text-muted">Horario disponible: 07:00 a 18:00</small>
                </div>

                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo</label>
                    <textarea name="motivo" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Solicitar Cita</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const esCliente = <?php echo $esClienteJS; ?>;
        const tieneCitaActiva = <?php echo $tieneCitaActivaJS; ?>;
        const esAdmin = <?php echo $esAdmin ? 'true' : 'false'; ?>;
        const citas = <?php echo $citasJSON; ?>;
        const calendarTitle = document.getElementById("calendar-title");
        const calendar = document.getElementById("gridcalendar");
        let currentDate = new Date();

        function renderCalendar(date) {
            const year = date.getFullYear();
            const month = date.getMonth();
            const today = new Date();
            calendar.innerHTML = "";

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            calendarTitle.innerText = `${date.toLocaleString("es-ES", { month: "long" })} ${year}`;
            const fechaHoy = new Date();
            fechaHoy.setHours(0, 0, 0, 0);

            for (let i = 0; i < 42; i++) {
                const cell = document.createElement("div");
                let dia, mes = month, anio = year;
                let esDelMesActual = true;

                if (i < firstDay) {
                    dia = daysInPrevMonth - firstDay + i + 1;
                    mes = month - 1;
                    if (mes < 0) { mes = 11; anio -= 1; }
                    esDelMesActual = false;
                } else if (i >= firstDay + daysInMonth) {
                    dia = i - (firstDay + daysInMonth) + 1;
                    mes = month + 1;
                    if (mes > 11) { mes = 0; anio += 1; }
                    esDelMesActual = false;
                } else {
                    dia = i - firstDay + 1;
                }

                const fechaStr = `${anio}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
                const esHoy = fechaStr === today.toISOString().split('T')[0];
                if (esHoy && esDelMesActual) {
                    cell.classList.add("current-day");
                }

                const partes = fechaStr.split("-");
                const fechaIterada = new Date(partes[0], partes[1] - 1, partes[2]);
                fechaIterada.setHours(0, 0, 0, 0);

                const cantidad = citas.filter(c => c.fecha === fechaStr && c.estado !== 'completado').length;

                cell.innerHTML = `<strong>${dia}</strong>`;

                if (esAdmin && cantidad > 0 && esDelMesActual) {
                    const textoCitas = cantidad === 1 ? "1 cita pendiente" : `${cantidad} citas pendientes`;
                    cell.innerHTML += `<div class='contador-citas'>${textoCitas}</div>`;
                    cell.addEventListener("click", () => mostrarCitasDelDia(fechaStr));
                }

                if (esCliente && !tieneCitaActiva && esDelMesActual && fechaIterada >= fechaHoy) {
                    const diaSemana = new Date(anio, mes, dia).getDay();
                    if (diaSemana >= 1 && diaSemana <= 5) {
                        cell.innerHTML += `<div class='contador-citas' style='background-color:#198754;'>Disponible</div>`;
                        cell.addEventListener("click", () => agendarCita(fechaStr));
                    }
                }

                calendar.appendChild(cell);
            }
        }

        const formCita = document.querySelector("#modalAgendarCita form");
        const inputFecha = document.getElementById("inputFechaCita");
        const inputHora = formCita.querySelector('input[name="hora"]');

        formCita.addEventListener("submit", function (e) {
            const fechaSeleccionada = new Date(inputFecha.value);
            const horaSeleccionada = inputHora.value;

            const ahora = new Date();

            // Si la fecha es hoy
            if (fechaSeleccionada.toDateString() === ahora.toDateString()) {
                const [horas, minutos] = horaSeleccionada.split(":");
                const horaIngresada = new Date();
                horaIngresada.setHours(parseInt(horas), parseInt(minutos), 0, 0);

                if (horaIngresada < ahora) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "warning",
                        title: "Hora no válida",
                        text: "No puedes agendar una cita en una hora que ya pasó.",
                        confirmButtonText: "Entendido"
                    });
                }
            }
        });

        function mostrarCitasDelDia(fecha) {
            const citasDelDia = citas.filter(c => c.fecha === fecha && c.estado !== 'completado');
            const contenedor = document.getElementById("contenidoCitasDia");

            if (!contenedor) return;

            contenedor.innerHTML = "";

            if (citasDelDia.length === 0) {
                contenedor.innerHTML = '<p class="text-muted text-center">No hay citas para este día.</p>';
            } else {
                citasDelDia.forEach(cita => {
                    const div = document.createElement("div");
                    div.className = "border rounded p-3 mb-3";
                    div.innerHTML = `
                    <p><strong>Cliente:</strong> ${cita.cliente}</p>
                    <p><strong>Hora:</strong> ${cita.hora}</p>
                    <p><strong>Motivo:</strong> ${cita.motivo}</p>
                    <form method="post">
                        <input type="hidden" name="id_completar" value="${cita.id_cita}">
                        <button type="submit" class="btn btn-success btn-sm mt-2">Completar</button>
                    </form>
                `;
                    contenedor.appendChild(div);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById("modalCitasDelDia"));
            modal.show();
        }

        function agendarCita(fecha) {
            document.getElementById("inputFechaCita").value = fecha;
            const modal = new bootstrap.Modal(document.getElementById("modalAgendarCita"));
            modal.show();
        }
        function prevMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        }
        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        }

        // Expone las funciones al HTML (botones onclick)
        window.prevMonth = prevMonth;
        window.nextMonth = nextMonth;

        renderCalendar(currentDate);
    });
</script>