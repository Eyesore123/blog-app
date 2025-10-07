<?php
// ============================
//  Secure Admin Access
// ============================
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';
if (!$validToken || $providedToken !== $validToken) die("Unauthorized");

// ============================
//  Database Connection
// ============================
$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) die("DATABASE_URL is not set!");
$dbParts = parse_url($databaseUrl);
$conn = pg_connect(sprintf(
    "host=%s port=%d dbname=%s user=%s password=%s",
    $dbParts['host'] ?? '',
    $dbParts['port'] ?? 5432,
    ltrim($dbParts['path'] ?? '', '/'),
    $dbParts['user'] ?? '',
    $dbParts['pass'] ?? ''
));
if (!$conn) die("Connection failed: " . pg_last_error());

// ============================
//  Helper: Safe Query
// ============================
function safe_query($conn, $sql, $params = []) {
    $result = pg_query_params($conn, $sql, $params);
    if ($result === false) die("Database error: " . pg_last_error($conn));
    return $result;
}

// ============================
//  Handle Reorder via AJAX FIRST
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['ajax'] ?? '') === 'reorder') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (is_array($data)) {
        foreach ($data as $order => $id) {
            safe_query($conn, "UPDATE trivia SET sort_order=$1 WHERE id=$2", [(int)$order + 1, (int)$id]);
        }
    }
    echo json_encode(['status' => 'ok']);
    exit;
}

// ============================
//  Handle Form Actions (Add/Update/Delete)
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $label = $_POST['label'] ?? '';
    $value = $_POST['value'] ?? '';

    if ($action === 'add') {
        safe_query($conn, "INSERT INTO trivia (label,value,created_at,updated_at,sort_order)
                           VALUES ($1,$2,NOW(),NOW(),
                                  COALESCE((SELECT MAX(sort_order)+1 FROM trivia), 1))",
                                  [$label, $value]);
    } elseif ($action === 'update' && $id) {
        safe_query($conn, "UPDATE trivia SET label=$1, value=$2, updated_at=NOW() WHERE id=$3", [$label, $value, $id]);
    } elseif ($action === 'delete' && $id) {
        safe_query($conn, "DELETE FROM trivia WHERE id=$1", [$id]);
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($providedToken));
    exit;
}

// ============================
//  Auto-fix sort_order if all zeros
// ============================
$check = pg_fetch_assoc(safe_query($conn,
    "SELECT COUNT(*) AS total, SUM(CASE WHEN sort_order = 0 THEN 1 ELSE 0 END) AS zero_count FROM trivia"
));
if ($check['total'] > 0 && $check['total'] == $check['zero_count']) {
    safe_query($conn, "
        WITH ordered AS (
            SELECT id, ROW_NUMBER() OVER (ORDER BY id) AS rn
            FROM trivia
        )
        UPDATE trivia
        SET sort_order = ordered.rn
        FROM ordered
        WHERE trivia.id = ordered.id
    ");
}

// ============================
//  Fetch Trivia
// ============================
$triviaList = pg_fetch_all(safe_query($conn, "SELECT * FROM trivia ORDER BY sort_order ASC, id ASC")) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Trivia</title>
<style>
body { font-family: system-ui,sans-serif; margin: 2rem; background: #f6f8fa; }
.container { max-width: 800px; margin:auto; padding:2rem; background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.08);}
h2,h3 { color:#222; margin-top:1rem;}
input[type="text"] { width:100%; padding:0.6rem; margin-bottom:0.5rem; border:1px solid #ccc; border-radius:4px;}
button { padding:0.5rem 1rem; background:#0074D9;color:#fff;border:none;border-radius:4px;cursor:pointer;}
button:hover { background:#005bb5; }
button.delete { background:#E74C3C; margin-left:3px;}
button.delete:hover { background:#C0392B; }
button.save-order { background:#2ECC71; margin-top:1rem;}
button.save-order:hover { background:#27AE60;}
.trivia-list { margin-top:1rem; }
.trivia-item { display:flex; align-items:center; justify-content:space-between; background:#fafafa;border:1px solid #ddd;border-radius:6px;padding:0.5rem 0.75rem;margin-bottom:0.5rem; cursor:grab; transition:background 0.2s;}
.trivia-item.dragging { opacity:0.6; background:#eef; }
.trivia-item form.inline { display:inline-flex; align-items:center; gap:0.3rem; flex:1;}
.drag-handle { cursor:grab;color:#888;font-size:1.2rem;margin-right:0.5rem;user-select:none;}
</style>
</head>
<body>
<div class="container">
<h2>Manage Trivia</h2>

<h3>Add New Trivia</h3>
<form method="post">
    <input type="hidden" name="action" value="add">
    <input type="text" name="label" placeholder="Label" required>
    <input type="text" name="value" placeholder="Value" required>
    <button type="submit">Add</button>
</form>

<h3>Existing Trivia (drag to reorder)</h3>
<?php if (!$triviaList): ?>
    <p>No trivia added yet.</p>
<?php else: ?>
<div class="trivia-list" id="triviaList">
    <?php foreach($triviaList as $item): ?>
        <div class="trivia-item" draggable="true" data-id="<?= $item['id'] ?>">
            <span class="drag-handle">☰</span>
            <form method="post" class="inline">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <input type="text" name="label" value="<?= htmlspecialchars($item['label']) ?>" required>
                <input type="text" name="value" value="<?= htmlspecialchars($item['value']) ?>" required>
                <button type="submit">Update</button>
            </form>
            <form method="post" class="inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <button type="submit" class="delete" onclick="return confirm('Delete this trivia?')">Delete</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<button class="save-order" id="saveOrderBtn">💾 Save Order</button>
<?php endif; ?>
</div>

<script>
const list = document.getElementById('triviaList');
const saveBtn = document.getElementById('saveOrderBtn');

let dragEl = null;
if(list){
  list.addEventListener('dragstart', e => {
    if(e.target.classList.contains('trivia-item')) {
      dragEl = e.target;
      dragEl.classList.add('dragging');
    }
  });
  list.addEventListener('dragend', e => {
    if(dragEl) dragEl.classList.remove('dragging');
    dragEl=null;
  });
  list.addEventListener('dragover', e => {
    e.preventDefault();
    const afterEl = getDragAfterElement(list, e.clientY);
    if(afterEl==null) list.appendChild(dragEl);
    else list.insertBefore(dragEl, afterEl);
  });
}

function getDragAfterElement(container, y){
  const els = [...container.querySelectorAll('.trivia-item:not(.dragging)')];
  return els.reduce((closest, child)=>{
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height/2;
    if(offset<0 && offset>closest.offset) return {offset, element:child};
    return closest;
  }, {offset:Number.NEGATIVE_INFINITY}).element;
}

if(saveBtn){
  saveBtn.addEventListener('click', async ()=>{
    const order = [...list.querySelectorAll('.trivia-item')].map(el=>el.dataset.id);
    saveBtn.textContent='Saving...';
    try{
      const res = await fetch('?token=<?= urlencode($providedToken) ?>&ajax=reorder',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(order)
      });
      if(res.ok){
        saveBtn.textContent='✅ Order Saved';
        setTimeout(()=>saveBtn.textContent='💾 Save Order',2000);
      } else throw 'Failed';
    }catch(e){
      alert('Failed to save order');
      saveBtn.textContent='💾 Save Order';
    }
  });
}
</script>
</body>
</html>
