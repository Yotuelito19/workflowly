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
$stmt = $db->query("SELECT idEstado, nombre FROM Estado ORDER BY nombre ASC");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin — Gestor de Eventos</title>
  <link rel="stylesheet" href="../../assets/css/admin.css">
  <style>
    .admin-wrap{max-width:1100px;margin:24px auto;padding:16px;background:#fff;border:1px solid #ddd;border-radius:10px}
    table{width:100%;border-collapse:collapse;margin-top:16px}
    th,td{border:1px solid #eee;padding:8px;text-align:left}
    .fila{display:flex;gap:8px;margin-bottom:8px}
    input,select,textarea{padding:8px;width:100%}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .actions{display:flex;gap:8px}
    .danger{background:#e74c3c;color:#fff;border:none;padding:6px 10px;cursor:pointer}
    .primary{background:#3498db;color:#fff;border:none;padding:8px 12px;cursor:pointer}
    .actions .btn-inactive{
  background:#f1c40f;
  color:#111;
  border:none;
  padding:6px 10px;
  cursor:pointer;
}
.actions .btn-inactive:hover{
  opacity:.9;
}

  </style>
</head>
<body>
  <div class="admin-wrap">
    <h1>Gestor de eventos</h1>

    <h2>Crear / Editar</h2>
    <form id="form-evento" enctype="multipart/form-data">
      <input type="hidden" name="idEvento" id="idEvento">
      <div class="grid-2">
        <div><label>Nombre</label><input name="nombre" id="nombre" required></div>
        <div><label>Tipo</label><input name="tipo" id="tipo" required></div>
      </div>
      <div class="grid-2">
        <div><label>Fecha inicio</label><input type="datetime-local" name="fechaInicio" id="fechaInicio" required step="1"></div>
        <div><label>Fecha fin</label><input type="datetime-local" name="fechaFin" id="fechaFin" required step="1"></div>
      </div>
     <div class="grid-2">
  <div>
    <label>Ubicación (texto)</label>
    <input name="ubicacion" id="ubicacion">
  </div>
  <div>
    <label>Estado</label>
    <select name="idEstadoEvento" id="idEstadoEvento">
      <?php foreach($estados as $e): ?>
        <option value="<?= (int)$e['idEstado'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- 🔽 NUEVO BLOQUE -->
<div class="grid-2">
  <div>
    <label>Lugar (BD)</label>
    <select name="idLugar" id="idLugar">
      <option value="">-- Selecciona lugar --</option>
      <!-- se rellena por JS -->
    </select>
    <button type="button" id="btnNuevoLugar">+ Nuevo lugar</button>
  </div>
  <div>
    <label>Organizador</label>
    <select name="idOrganizador" id="idOrganizador">
      <option value="">-- Selecciona organizador --</option>
      <!-- se rellena por JS -->
    </select>
    <button type="button" id="btnNuevoOrganizador">+ Nuevo organizador</button>
  </div>
</div>

      <div class="grid-2">
        <div><label>Aforo total</label><input type="number" name="aforoTotal" id="aforoTotal" min="1" required></div>
        <div>
          <label>Entradas disponibles</label>
          <input type="number" name="entradasDisponibles" id="entradasDisponibles" min="0" required>
        </div>
      </div>
      <div class="fila"><label>Descripción</label></div>
      <div class="fila"><textarea name="descripcion" id="descripcion" rows="4"></textarea></div>
      <div class="fila"><label>Imagen principal</label><input type="file" name="imagen" id="imagen" accept="image/*"></div>
      <button class="primary" type="submit">Guardar</button>
      <button type="button" id="btn-reset">Limpiar</button>
    </form>

    <h2>Eventos</h2>
    <table>
      <thead><tr><th>ID</th><th>Nombre</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Stock</th><th>Acciones</th></tr></thead>
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
<script src="../../assets/js/main.js"></script>

<script>
// --- Helpers JS ---
const toLocal = (s) => {
  if (!s) return '';
  const t = s.replace(' ', 'T');
  return /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/.test(t) ? t : t.slice(0,16);
};

async function fetchJSON(url, options){
  const res = await fetch(url, options);
  const status = res.status;
  const txt = await res.text();

  // 1) intento directo
  try { return JSON.parse(txt); } catch {}

  // 2) recorta BOM/espacios
  const trimmed = txt.trim();
  try { return JSON.parse(trimmed); } catch {}

  // 3) intenta rescatar el ÚLTIMO objeto JSON de la respuesta
  const m = trimmed.match(/\{[\s\S]*\}$/);
  if (m) {
    try { return JSON.parse(m[0]); } catch {}
  }

  // 4) si el servidor devolvió 200 pero no parsea, acepta éxito si contiene "ok":true
  if (status >= 200 && status < 300 && /"ok"\s*:\s*true/.test(txt)) {
    return { ok: true, _raw: txt };
  }

  console.error('Respuesta no JSON:', { status, txt });
  return { ok: false, error: `Respuesta no válida (HTTP ${status})`, debug: txt };
}



const $form = document.getElementById('form-evento');
const $reset = document.getElementById('btn-reset');
const $tbody = document.getElementById('tbody');

// helper por si el clic cae en <span> o algo dentro del botón
function hasClassInPath(target, className){
  return target.classList?.contains(className) || target.closest('.' + className);
}

// --- LISTENER ÚNICO PARA LA TABLA ---
$tbody.addEventListener('click', async (e) => {
  const tr = e.target.closest('tr');
  if (!tr) return;
  const data = JSON.parse(tr.getAttribute('data-json'));

  // 1) EDITAR
  if (hasClassInPath(e.target, 'btn-edit')) {
    document.getElementById('idEvento').value = data.idEvento;
    document.getElementById('nombre').value = data.nombre || '';
    document.getElementById('tipo').value = data.tipo || '';
    document.getElementById('fechaInicio').value = toLocal(data.fechaInicio || '');
    document.getElementById('fechaFin').value   = toLocal(data.fechaFin || '');
    document.getElementById('ubicacion').value = data.ubicacion || '';
    document.getElementById('idEstadoEvento').value = data.idEstadoEvento || '';
    document.getElementById('aforoTotal').value = data.aforoTotal || 0;
    document.getElementById('entradasDisponibles').value = data.entradasDisponibles || 0;
    document.getElementById('descripcion').value = data.descripcion || '';
    await cargarLugares();
await cargarOrganizadores();
document.getElementById('idLugar').value = data.idLugar || '';
document.getElementById('idOrganizador').value = data.idOrganizador || '';
    window.scrollTo({top:0, behavior:'smooth'});
    return;
  }

  // 2) INACTIVAR
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

  // 3) ELIMINAR
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

// --- Reset form ---
$reset.addEventListener('click', () => {
  $form.reset();
  document.getElementById('idEvento').value = '';
});

// --- Submit crear/editar ---
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

// Cargar combos al entrar en la página (modo crear)
document.addEventListener('DOMContentLoaded', async () => {
  await cargarLugares();
  await cargarOrganizadores();
});



</script>
</body>
</html>
