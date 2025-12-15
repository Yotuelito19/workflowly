<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Evento.php';

if (!is_admin()) {
    redirect('/views/login.php');
}

$database = new Database();
$db = $database->getConnection();
$eventoModel = new Evento($db);

// Listado
$eventos = $eventoModel->listarTodos(100, 0);

// Estados para select
$stmt = $db->query("SELECT idEstado, nombre FROM Estado WHERE tipoEntidad = 'General' ORDER BY nombre ASC");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Gestor de Eventos - WorkFlowly</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="admin-wrap">
        <h1>Gestor de eventos</h1>

        <h2>Crear / Editar</h2>
        <form id="form-evento" enctype="multipart/form-data">
            <input type="hidden" name="idEvento" id="idEvento">
            <div class="grid-2">
                <div>
                    <label>Nombre</label>
                    <input name="nombre" id="nombre" required>
                </div>
                <div>
                    <label>Tipo</label>
                    <select name="tipo" id="tipo" required>
                        <option value="">-- Selecciona --</option>
                        <option>Concierto</option>
                        <option>Festival</option>
                        <option>Teatro</option>
                        <option>Deporte</option>
                        <option>Cultural</option>
                        <option>Otro</option>
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div>
                    <label>Fecha inicio</label>
                    <input type="datetime-local" name="fechaInicio" id="fechaInicio" required step="1">
                </div>
                <div>
                    <label>Fecha fin</label>
                    <input type="datetime-local" name="fechaFin" id="fechaFin" required step="1">
                </div>
            </div>
            <div class="grid-2">
                <div>
                    <label>Estado</label>
                    <select name="idEstadoEvento" id="idEstadoEvento">
                        <?php foreach($estados as $e): ?>
                            <option value="<?= (int)$e['idEstado'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Organizador</label>
                    <select name="idOrganizador" id="idOrganizador">
                        <option value="">-- Selecciona organizador --</option>
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div>
                    <label>Lugar (BD)</label>
                    <select name="idLugar" id="idLugar">
                        <option value="">-- Selecciona lugar --</option>
                    </select>
                    <button type="button" id="btnNuevoLugar">+ Nuevo lugar</button>
                </div>
                <div class="event-form-right">
                    <fieldset class="ticket-box">
                        <legend>Tipos de entrada y precios</legend>
                        <div class="ticket-tools">
                            <select id="ticketSelect">
                                <option value="">-- Tipos existentes --</option>
                            </select>
                            <button type="button" id="ticketNew" class="secondary">Nuevo</button>
                            <button type="button" id="ticketDelete" class="secondary" disabled>Eliminar</button>
                        </div>
                        <small class="muted">"Entradas disponibles" del evento se calcula como la suma de cantidades.</small>
                    </fieldset>
                </div>
            </div>
            <div class="grid-2">
                <div>
                    <label>Aforo total</label>
                    <input type="number" name="aforoTotal" id="aforoTotal" min="1" required>
                </div>
                <div>
                    <label>Entradas disponibles</label>
                    <input type="number" name="entradasDisponibles" id="entradasDisponibles" min="0" required>
                </div>
            </div>
            <div class="fila">
                <label>Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4"></textarea>
            </div>
            <div class="fila">
                <label>Imagen principal</label>
                <input type="file" name="imagen" id="imagen" accept="image/*">
            </div>
            <button class="primary" type="submit">Guardar</button>
            <button type="button" id="btn-reset">Limpiar</button>
        </form>

        <h2>Eventos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php foreach($eventos as $ev): ?>
                    <tr data-json='<?= json_encode($ev, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                        <td><?= (int)$ev['idEvento'] ?></td>
                        <td><?= htmlspecialchars($ev['nombre']) ?></td>
                        <td><?= htmlspecialchars($ev['fechaInicio']) ?></td>
                        <td><?= htmlspecialchars($ev['fechaFin']) ?></td>
                        <td>
                            <?php
                                $estado = $ev['estado_nombre'] ?? '';
                                if (strcasecmp($estado, 'Inactivo') === 0) {
                                    echo '<span style="color:#e74c3c;font-weight:600">Inactivo</span>';
                                } else {
                                    echo htmlspecialchars($estado);
                                }
                            ?>
                        </td>
                        <td><?= (int)$ev['entradasDisponibles'] ?></td>
                        <td class="actions">
                            <button class="primary btn-edit">Editar</button>
                            <button class="btn-inactive">Desactivar</button>
                            <button class="danger btn-del">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Nuevo Lugar -->
    <div id="modalLugar" class="modal hidden">
        <div class="modal-content">
            <h2>Nuevo lugar</h2>
            <label>Nombre</label>
            <input type="text" id="lugarNombre" required>
            <label>Dirección</label>
            <input type="text" id="lugarDireccion">
            <label>Ciudad</label>
            <input type="text" id="lugarCiudad">
            <label>País</label>
            <input type="text" id="lugarPais" value="España">
            <label>Capacidad (aforo)</label>
            <input type="number" id="lugarCapacidad" min="1" required>
            <div class="inline-group">
                <input type="checkbox" id="lugarAcceso">
                <label for="lugarAcceso">Acceso para personas con movilidad reducida</label>
            </div>
            <label>Parking</label>
            <input type="text" id="lugarParking" placeholder="Parking propio, calle, concertado…">
            <label>Transporte público más cercano</label>
            <input type="text" id="lugarTransporte" placeholder="Metro L3 – Estación X; Bus 25, 33…">
            <label>Enlace al mapa</label>
            <input type="url" id="lugarMapa" placeholder="https://maps.google.com/…">
            <div class="modal-actions">
                <button id="btnGuardarLugar" class="btn-primary">Guardar</button>
                <button id="btnCerrarLugar" class="btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Tipo de Entrada -->
    <div id="modalTipo" class="modal hidden">
        <div class="modal-content">
            <h2>Nuevo tipo de entrada</h2>
            <label for="tipoNombre">Nombre</label>
            <input type="text" id="tipoNombre" placeholder="General, VIP…" required>
            <label for="tipoDescripcion">Descripción</label>
            <textarea id="tipoDescripcion" rows="3" placeholder="Ej: acceso general sin asiento, incluye consumición..."></textarea>
            <label for="tipoPrecio">Precio</label>
            <input type="number" id="tipoPrecio" step="0.01" min="0" required>
            <label for="tipoCantidad">Cantidad</label>
            <input type="number" id="tipoCantidad" min="0" required>
            <div class="modal-actions">
                <button id="btnGuardarTipo" class="btn-primary">Guardar</button>
                <button id="btnCerrarTipo" class="btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        // Helpers JS
        const toLocal = (s) => {
            if (!s) return '';
            const t = s.replace(' ', 'T');
            return /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/.test(t) ? t : t.slice(0,16);
        };

        async function fetchJSON(url, options){
            const res = await fetch(url, options);
            const status = res.status;
            const txt = await res.text();

            try { return JSON.parse(txt); } catch {}

            const trimmed = txt.trim();
            try { return JSON.parse(trimmed); } catch {}

            const m = trimmed.match(/\{[\s\S]*\}$/);
            if (m) {
                try { return JSON.parse(m[0]); } catch {}
            }

            if (status >= 200 && status < 300 && /"ok"\s*:\s*true/.test(txt)) {
                return { ok: true, _raw: txt };
            }

            console.error('Respuesta no JSON:', { status, txt });
            return { ok: false, error: `Respuesta no válida (HTTP ${status})`, debug: txt };
        }

        const $form  = document.getElementById('form-evento');
        const $reset = document.getElementById('btn-reset');
        const $tbody = document.getElementById('tbody');

        // Helper por si el clic cae en <span> o algo dentro del botón
        function hasClassInPath(target, className){
            return target.classList?.contains(className) || target.closest('.' + className);
        }

        // Tipos de entrada
        const getIdEventoActual = () =>
            parseInt(document.getElementById('idEvento')?.value || '0', 10);

        async function cargarTipos(idEventoParam) {
            const id = idEventoParam || getIdEventoActual();
            const sel = document.getElementById('ticketSelect');
            if (!sel) return;

            if (!id) {
                sel.innerHTML = '<option value="">Guarda el evento primero</option>';
                return;
            }

            const res = await fetch(`../../api/admin/tipos/listar.php?idEvento=${encodeURIComponent(id)}`);
            const data = await res.json();

            sel.innerHTML = '<option value="">-- Tipos existentes --</option>';
            data.forEach(t => {
                const o = document.createElement('option');
                o.value = t.idTipoEntrada;
                o.textContent = `${t.nombre} — ${t.precio}€ — ${t.cantidadDisponible} uds`;
                sel.appendChild(o);
            });

            // Actualizar "Entradas disponibles"
            const total = data.reduce((sum, t) => sum + (parseInt(t.cantidadDisponible || '0', 10)), 0);
            const aforo = parseInt(document.getElementById('aforoTotal').value || '0', 10);
            document.getElementById('entradasDisponibles').value = aforo ? Math.min(total, aforo) : total;
        }

        // Abrir modal de tipo
        document.getElementById('ticketNew')?.addEventListener('click', () => {
            const id = getIdEventoActual();
            if (!id) { alert('Primero guarda el evento.'); return; }
            document.getElementById('tipoNombre').value = '';
            document.getElementById('tipoPrecio').value = '';
            document.getElementById('tipoCantidad').value = '';
            document.getElementById('modalTipo').classList.remove('hidden');
        });

        // Cerrar modal de tipo
        document.getElementById('btnCerrarTipo')?.addEventListener('click', () => {
            document.getElementById('modalTipo').classList.add('hidden');
        });

        // Guardar tipo
        document.getElementById('btnGuardarTipo')?.addEventListener('click', async () => {
            const idEvento = getIdEventoActual();
            const nombre   = document.getElementById('tipoNombre').value.trim();
            const descripcion = document.getElementById('tipoDescripcion').value;
            const precio   = parseFloat(document.getElementById('tipoPrecio').value || '0');
            const cantidad = parseInt(document.getElementById('tipoCantidad').value || '0', 10);

            if (!nombre) { alert('Pon un nombre'); return; }

            const res = await fetch('../../api/admin/tipos/crear.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    idEvento,
                    nombre,
                    descripcion, 
                    precio,
                    cantidadDisponible: cantidad
                })
            });
            const json = await res.json();
            if (!json.ok) {
                alert(json.msg || 'No se pudo guardar');
                console.error(json);
                return;
            }

            // Si el server recortó la cantidad, avisa
            if (json.cantidadGuardada !== undefined &&
                json.cantidadGuardada < parseInt(document.getElementById('tipoCantidad').value || '0', 10)) {
                alert('Se guardó el tipo, pero la cantidad se ha ajustado al aforo disponible (' + json.cantidadGuardada + ').');
            }

            document.getElementById('modalTipo').classList.add('hidden');
            await cargarTipos(idEvento);
        });

        // Listener único para la tabla
        $tbody.addEventListener('click', async (e) => {
            const tr = e.target.closest('tr');
            if (!tr) return;
            const data = JSON.parse(tr.getAttribute('data-json'));

            // Editar
            if (hasClassInPath(e.target, 'btn-edit')) {
                document.getElementById('idEvento').value = data.idEvento;
                document.getElementById('nombre').value = data.nombre || '';
                document.getElementById('tipo').value = data.tipo || '';
                document.getElementById('fechaInicio').value = toLocal(data.fechaInicio || '');
                document.getElementById('fechaFin').value   = toLocal(data.fechaFin || '');
                document.getElementById('idEstadoEvento').value = data.idEstadoEvento || '';
                document.getElementById('aforoTotal').value = data.aforoTotal || 0;
                document.getElementById('entradasDisponibles').value = data.entradasDisponibles || 0;
                document.getElementById('descripcion').value = data.descripcion || '';

                await cargarLugares();
                await cargarOrganizadores();
                document.getElementById('idLugar').value = data.idLugar || '';
                document.getElementById('idOrganizador').value = data.idOrganizador || '';

                await cargarTipos(data.idEvento);

                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            // Desactivar
            if (hasClassInPath(e.target, 'btn-inactive')) {
                if (!confirm('¿Marcar este evento como INACTIVO?')) return;

                const fd = new FormData();
                fd.append('idEvento', data.idEvento);

                const json = await fetchJSON('../../api/admin/events/inactivate.php', {
                    method: 'POST',
                    body: fd
                });

                if (!json.ok) {
                    alert((json.error || 'Error inactivando') + (json.debug ? '\n\n' + json.debug.slice(0,400) : ''));
                    return;
                }

                location.reload();
                return;
            }

            // Eliminar
            if (hasClassInPath(e.target, 'btn-del')) {
                if (!confirm('¿Eliminar este evento definitivamente?')) return;

                const fd = new FormData();
                fd.append('idEvento', data.idEvento);

                const json = await fetchJSON('../../api/admin/events/delete.php', {
                    method: 'POST',
                    body: fd
                });

                if (!json.ok) {
                    alert((json.error || 'Error eliminando') + (json.debug ? '\n\n' + json.debug.slice(0,400) : ''));
                    return;
                }

                location.reload();
                return;
            }
        });

        // Reset form
        $reset.addEventListener('click', () => {
            $form.reset();
            document.getElementById('idEvento').value = '';
            const sel = document.getElementById('ticketSelect');
            if (sel) sel.innerHTML = '<option value="">Guarda el evento primero</option>';
        });

        // Submit crear/editar
        $form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData($form);
            const url = fd.get('idEvento')
                ? '../../api/admin/events/update.php'
                : '../../api/admin/events/create.php';

            const json = await fetchJSON(url, { method:'POST', body: fd });

            if (!json.ok) {
                alert((json.error || 'Error guardando') + (json.debug ? '\n\n' + json.debug.slice(0,400) : ''));
                return;
            }
            location.reload();
        });

        // Cargar combos al entrar en la página
        document.addEventListener('DOMContentLoaded', async () => {
            await cargarLugares();
            await cargarOrganizadores();
        });

        // Auto-aforo al cambiar Lugar
        document.addEventListener('change', (e) => {
            if (e.target && e.target.id === 'idLugar') {
                const opt = e.target.selectedOptions[0];
                const cap = opt?.dataset?.capacidad ? parseInt(opt.dataset.capacidad, 10) : 0;
                if (cap > 0) {
                    document.getElementById('aforoTotal').value = cap;
                    document.getElementById('entradasDisponibles').value = cap;
                }
            }
        });

        // Modal Lugar - Abrir
        document.getElementById('btnNuevoLugar')?.addEventListener('click', () => {
            document.getElementById('modalLugar').classList.remove('hidden');
        });

        // Modal Lugar - Cerrar
        document.getElementById('btnCerrarLugar')?.addEventListener('click', () => {
            document.getElementById('modalLugar').classList.add('hidden');
        });

        // Modal Lugar - Guardar
        document.getElementById('btnGuardarLugar')?.addEventListener('click', async () => {
            const payload = {
                nombre: document.getElementById('lugarNombre').value.trim(),
                direccion: document.getElementById('lugarDireccion').value.trim(),
                ciudad: document.getElementById('lugarCiudad').value.trim(),
                pais: document.getElementById('lugarPais').value.trim(),
                capacidad: parseInt(document.getElementById('lugarCapacidad').value || '0', 10),
                accesoDiscapacitados: document.getElementById('lugarAcceso').checked ? 1 : 0,
                parking: document.getElementById('lugarParking').value.trim(),
                transportePublico: document.getElementById('lugarTransporte').value.trim(),
                mapaUrl: document.getElementById('lugarMapa').value.trim()
            };

            const res = await fetch('../../api/admin/lugares/crear.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (!data.ok) { alert(data.msg || 'Error al crear lugar'); return; }

            await cargarLugares();
            document.getElementById('idLugar').value = data.idLugar;
            document.getElementById('aforoTotal').value = data.capacidad || 0;
            document.getElementById('entradasDisponibles').value = data.capacidad || 0;
            document.getElementById('modalLugar').classList.add('hidden');
        });

        // Habilitar/deshabilitar "Eliminar" según haya selección
        document.getElementById('ticketSelect')?.addEventListener('change', (e) => {
            const has = !!e.target.value;
            document.getElementById('ticketDelete').disabled = !has;
        });

        // Eliminar tipo seleccionado
        document.getElementById('ticketDelete')?.addEventListener('click', async () => {
            const idEvento = parseInt(document.getElementById('idEvento').value || '0', 10);
            const idTipo   = parseInt(document.getElementById('ticketSelect').value || '0', 10);
            if (!idEvento || !idTipo) return;

            if (!confirm('¿Eliminar este tipo de entrada?')) return;

            const fd = new FormData();
            fd.append('idEvento', idEvento);
            fd.append('idTipoEntrada', idTipo);

            const res  = await fetch('../../api/admin/tipos/eliminar.php', { method:'POST', body: fd });
            const json = await res.json();

            if (!json.ok) {
                alert(json.msg || 'No se pudo eliminar');
                return;
            }

            await cargarTipos(idEvento);
            document.getElementById('ticketDelete').disabled = true;
        });
    </script>
</body>
</html>