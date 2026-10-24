<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('403 Forbidden: Admin access required.');
}
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$uploadDir = __DIR__ . '/uploads/event_posters/';
$uploadUrl = 'uploads/event_posters/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$message = $error = '';

function h($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function audit(PDO $pdo, string $action, string $details): void {
    try {
        $s = $pdo->prepare('INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)');
        $s->execute([$_SESSION['user_id'] ?? null, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) {}
}
function deletePoster(?string $path): void {
    if (!$path) return;
    $base = realpath(__DIR__ . '/uploads/event_posters');
    $full = realpath(__DIR__ . '/' . $path);
    if ($base && $full && str_starts_with($full, $base . DIRECTORY_SEPARATOR) && is_file($full)) unlink($full);
}
function posterUpload(array $f, string $dir, string $url): ?string {
    if (!isset($f['error']) || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Poster upload failed.');
    if ($f['size'] > 2 * 1024 * 1024) throw new RuntimeException('Poster must be 2 MB or smaller.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $types = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
    if (!isset($types[$mime])) throw new RuntimeException('Only JPG, PNG, and WEBP posters are allowed.');
    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir . $name)) throw new RuntimeException('Could not save poster.');
    return $url . $name;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? '')))
            throw new RuntimeException('Security token expired. Refresh and try again.');

        if (($_POST['action'] ?? '') === 'save') {
            $id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT) ?: null;
            $name = trim((string)($_POST['event_name'] ?? ''));
            $date = trim((string)($_POST['event_date'] ?? ''));
            $venue = trim((string)($_POST['venue'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            if ($name === '' || $date === '' || $venue === '') throw new RuntimeException('Event name, date, and venue are required.');
            $dt = DateTime::createFromFormat('Y-m-d', $date);
            if (!$dt || $dt->format('Y-m-d') !== $date) throw new RuntimeException('Enter a valid event date.');

            $old = null;
            if ($id) {
                $s = $pdo->prepare('SELECT poster_path FROM events WHERE event_id = ?');
                $s->execute([$id]); $row = $s->fetch(PDO::FETCH_ASSOC);
                if (!$row) throw new RuntimeException('Event not found.');
                $old = $row['poster_path'] ?? null;
            }
            $new = posterUpload($_FILES['poster'] ?? [], $uploadDir, $uploadUrl);
            $path = $new ?? $old;
            if ($id) {
                $s = $pdo->prepare('UPDATE events SET event_name=?, event_date=?, venue=?, description=?, poster_path=? WHERE event_id=?');
                $s->execute([$name, $date, $venue, $description, $path, $id]);
                audit($pdo, 'event_updated', "Updated event #$id: $name");
                if ($new && $old) deletePoster($old);
                $message = 'Event updated successfully.';
            } else {
                $s = $pdo->prepare('INSERT INTO events (event_name,event_date,venue,description,poster_path) VALUES (?,?,?,?,?)');
                $s->execute([$name, $date, $venue, $description, $path]);
                audit($pdo, 'event_created', 'Created event #' . $pdo->lastInsertId() . ": $name");
                $message = 'Event created successfully.';
            }
        } elseif (($_POST['action'] ?? '') === 'delete') {
            $id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
            if (!$id) throw new RuntimeException('Invalid event ID.');
            $s = $pdo->prepare('SELECT event_name, poster_path FROM events WHERE event_id=?');
            $s->execute([$id]); $row = $s->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new RuntimeException('Event not found.');
            $pdo->prepare('DELETE FROM events WHERE event_id=?')->execute([$id]);
            deletePoster($row['poster_path'] ?? null);
            audit($pdo, 'event_deleted', "Deleted event #$id: " . $row['event_name']);
            $message = 'Event deleted successfully.';
        }
    }
} catch (Throwable $e) {
    $error = $e instanceof PDOException ? 'Database error. Confirm poster_path exists in events.' : $e->getMessage();
}

$edit = null;
if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM events WHERE event_id=?');
    $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(PDO::FETCH_ASSOC) ?: null;
}
$q = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT * FROM events'; $params = [];
if ($q !== '') { $sql .= ' WHERE event_name LIKE ? OR venue LIKE ?'; $params = ["%$q%", "%$q%"]; }
$sql .= ' ORDER BY event_date DESC, event_id DESC';
$s = $pdo->prepare($sql); $s->execute($params); $events = $s->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Events | StudentHub</title>
<style>
*{box-sizing:border-box}body{font:16px Arial,sans-serif;background:#f5f7fb;color:#222;margin:0}main{max-width:1100px;margin:28px auto;padding:0 16px}.card{background:white;padding:20px;border-radius:12px;margin-bottom:20px;box-shadow:0 3px 14px #0000000b}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}label{display:block;font-weight:bold;margin:8px 0 6px}input,textarea{width:100%;padding:10px;border:1px solid #ccd2dc;border-radius:7px;font:inherit}textarea{min-height:80px}button,.btn{border:0;border-radius:7px;padding:10px 14px;background:#2367d1;color:white;text-decoration:none;cursor:pointer;display:inline-block}.del{background:#b42318}.alert{padding:12px;border-radius:8px;margin:12px 0}.ok{background:#e7f8ed}.bad{background:#ffeded}.muted{color:#667085}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;vertical-align:top}th{background:#f0f3f8}.poster{max-width:90px;max-height:75px;object-fit:cover}.actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.inline{display:inline}.wrap{overflow-x:auto}@media(max-width:650px){.grid{grid-template-columns:1fr}}
</style></head><body><main>
<h1>Manage Events</h1><p class="muted">Create, edit, search, and delete events. Posters: JPG, PNG, or WEBP, maximum 2 MB.</p>
<?php if($message): ?><div class="alert ok"><?=h($message)?></div><?php endif; ?><?php if($error): ?><div class="alert bad"><?=h($error)?></div><?php endif; ?>
<section class="card"><h2><?=$edit?'Edit Event':'Add Event'?></h2>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><input type="hidden" name="action" value="save"><input type="hidden" name="event_id" value="<?=h($edit['event_id']??'')?>">
<div class="grid"><div><label for="event_name">Event name *</label><input id="event_name" name="event_name" maxlength="150" required value="<?=h($edit['event_name']??'')?>"></div>
<div><label for="event_date">Event date *</label><input id="event_date" type="date" name="event_date" required value="<?=h($edit['event_date']??'')?>"></div>
<div><label for="venue">Venue *</label><input id="venue" name="venue" maxlength="150" required value="<?=h($edit['venue']??'')?>"></div>
<div><label for="poster">Poster (optional)</label><input id="poster" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
<?php if(!empty($edit['poster_path'])): ?><p><img class="poster" src="<?=h($edit['poster_path'])?>" alt="Current event poster"></p><?php endif; ?></div></div>
<label for="description">Description</label><textarea id="description" name="description"><?=h($edit['description']??'')?></textarea><p class="actions"><button><?=$edit?'Update Event':'Create Event'?></button><?php if($edit): ?><a class="btn" href="admin_events.php">Cancel</a><?php endif; ?></p>
</form></section>
<section class="card"><h2>Existing Events</h2><form class="actions" method="get" style="margin-bottom:14px"><input style="max-width:350px" name="q" placeholder="Search event name or venue" value="<?=h($q)?>"><button>Search</button><a class="btn" href="admin_events.php">Clear</a></form>
<div class="wrap"><table><thead><tr><th>ID</th><th>Poster</th><th>Event</th><th>Date</th><th>Venue</th><th>Actions</th></tr></thead><tbody>
<?php foreach($events as $e): ?><tr><td><?=(int)$e['event_id']?></td><td><?php if(!empty($e['poster_path'])): ?><img class="poster" src="<?=h($e['poster_path'])?>" alt="Event poster"><?php else: ?><span class="muted">None</span><?php endif; ?></td><td><strong><?=h($e['event_name'])?></strong><br><?=h($e['description']??'')?></td><td><?=h($e['event_date'])?></td><td><?=h($e['venue'])?></td><td><div class="actions"><a class="btn" href="?edit=<?=(int)$e['event_id']?>">Edit</a><form class="inline" method="post" onsubmit="return confirm('Delete this event?')"><input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="event_id" value="<?=(int)$e['event_id']?>"><button class="del">Delete</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$events): ?><tr><td colspan="6">No events found.</td></tr><?php endif; ?></tbody></table></div></section>
<p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p></main></body></html>
