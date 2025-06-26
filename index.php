<?php
// JSON dosya depolama sistemi
class TodoStorage {
    private $dataFile;
    
    public function __construct($filename = 'todos.json') {
        $this->dataFile = __DIR__ . '/' . $filename;
        $this->ensureDataFileExists();
    }
    
    private function ensureDataFileExists() {
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([], JSON_PRETTY_PRINT));
        }
    }
    
    public function getTodos() {
        $data = file_get_contents($this->dataFile);
        return json_decode($data, true) ?: [];
    }
    
    public function saveTodos($todos) {
        $json = json_encode($todos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Dosya kilitleme ile güvenli yazma
        $file = fopen($this->dataFile, 'w');
        if ($file && flock($file, LOCK_EX)) {
            fwrite($file, $json);
            fflush($file);
            flock($file, LOCK_UN);
            fclose($file);
            return true;
        }
        
        if ($file) {
            fclose($file);
        }
        
        return false;
    }
    
    public function addTodo($text) {
        $todos = $this->getTodos();
        $todo = [
            'id' => uniqid('todo_', true),
            'text' => htmlspecialchars(trim($text)),
            'completed' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        array_unshift($todos, $todo);
        return $this->saveTodos($todos);
    }
    
    public function toggleTodo($id) {
        $todos = $this->getTodos();
        $found = false;
        
        foreach ($todos as &$todo) {
            if ($todo['id'] === $id) {
                $todo['completed'] = !$todo['completed'];
                $todo['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($todo); // Referans temizle
        
        if ($found) {
            $result = $this->saveTodos($todos);
            // Debug log
            error_log("Toggle Todo - ID: $id, Found: " . ($found ? 'Yes' : 'No') . ", Saved: " . ($result ? 'Yes' : 'No'));
            return $result;
        }
        
        return false;
    }
    
    public function deleteTodo($id) {
        $todos = $this->getTodos();
        $todos = array_filter($todos, function($todo) use ($id) {
            return $todo['id'] !== $id;
        });
        return $this->saveTodos(array_values($todos));
    }
    
    public function editTodo($id, $newText) {
        $todos = $this->getTodos();
        foreach ($todos as &$todo) {
            if ($todo['id'] === $id) {
                $todo['text'] = htmlspecialchars(trim($newText));
                $todo['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($todo); // Referans temizle
        return $this->saveTodos($todos);
    }
    
    public function clearCompleted() {
        $todos = $this->getTodos();
        $todos = array_filter($todos, function($todo) {
            return !$todo['completed'];
        });
        return $this->saveTodos(array_values($todos));
    }
    
    public function getStats() {
        $todos = $this->getTodos();
        $total = count($todos);
        $completed = count(array_filter($todos, fn($t) => $t['completed']));
        
        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed
        ];
    }
    
    // Backup ve restore
    public function createBackup() {
        $backupFile = __DIR__ . '/backup_' . date('Y-m-d_H-i-s') . '.json';
        return copy($this->dataFile, $backupFile) ? $backupFile : false;
    }
    
    public function restoreBackup($backupFile) {
        if (file_exists($backupFile)) {
            return copy($backupFile, $this->dataFile);
        }
        return false;
    }
}

// TodoStorage örneği oluştur
$storage = new TodoStorage();

// Duplicate request kontrolü için session
session_start();

$action = $_GET['action'] ?? 'index';
$isTurboFrame = isset($_SERVER['HTTP_TURBO_FRAME']);
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Request ID oluştur (duplicate kontrolü için)
$requestId = $action . '_' . ($_GET['id'] ?? '') . '_' . time();
$lastRequestKey = 'last_request_' . $action;

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text = $_POST['text'] ?? '';
            if ($text && strlen($text) <= 100) {
                $storage->addTodo($text);
                
                if ($isTurboFrame || $isAjax) {
                    header('Content-Type: text/html');
                    renderTodoListFrame($storage);
                    exit;
                }
            }
        }
        break;
        
    case 'toggle':
        $id = $_GET['id'] ?? '';
        if ($id) {
            // Duplicate request kontrolü
            $currentRequest = 'toggle_' . $id;
            if (isset($_SESSION[$lastRequestKey]) && 
                $_SESSION[$lastRequestKey] === $currentRequest && 
                (time() - ($_SESSION[$lastRequestKey . '_time'] ?? 0)) < 2) {
                
                error_log("Duplicate toggle request ignored for ID: $id");
                
                if ($isTurboFrame || $isAjax) {
                    header('Content-Type: text/html');
                    renderTodoListFrame($storage);
                    exit;
                }
                break;
            }
            
            // Request'i kaydet
            $_SESSION[$lastRequestKey] = $currentRequest;
            $_SESSION[$lastRequestKey . '_time'] = time();
            
            $result = $storage->toggleTodo($id);
            
            // Debug: JSON dosyasının gerçek durumunu kontrol et
            $todos = $storage->getTodos();
            $currentTodo = array_filter($todos, fn($t) => $t['id'] === $id);
            $currentTodo = reset($currentTodo);
            
            error_log("Toggle result - ID: $id, Success: " . ($result ? 'Yes' : 'No') . ", Current status: " . ($currentTodo ? ($currentTodo['completed'] ? 'completed' : 'pending') : 'not found'));
            
            if ($isTurboFrame || $isAjax) {
                header('Content-Type: text/html');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                renderTodoListFrame($storage);
                exit;
            } else {
                // Normal sayfa yüklemesi - redirect et
                header('Location: ' . $_SERVER['PHP_SELF'] . '?_=' . time());
                exit;
            }
        }
        break;
        
    case 'delete':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $storage->deleteTodo($id);
            
            if ($isTurboFrame || $isAjax) {
                header('Content-Type: text/html');
                renderTodoListFrame($storage);
                exit;
            }
        }
        break;
        
    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $newText = $_POST['text'] ?? '';
            
            if ($id && $newText && strlen($newText) <= 100) {
                $storage->editTodo($id, $newText);
                
                if ($isTurboFrame || $isAjax) {
                    header('Content-Type: text/html');
                    renderTodoListFrame($storage);
                    exit;
                }
            }
        }
        break;
        
    case 'clear_completed':
        $storage->clearCompleted();
        
        if ($isTurboFrame || $isAjax) {
            header('Content-Type: text/html');
            renderTodoListFrame($storage);
            exit;
        }
        break;
        
    case 'backup':
        $backupFile = $storage->createBackup();
        if ($backupFile) {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="todo_backup_' . date('Y-m-d_H-i-s') . '.json"');
            readfile($backupFile);
            unlink($backupFile); // Geçici backup dosyasını sil
            exit;
        }
        break;
        
    case 'export':
        $todos = $storage->getTodos();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="todos_' . date('Y-m-d') . '.csv"');
        
        echo "ID,Text,Status,Created,Updated\n";
        foreach ($todos as $todo) {
            echo sprintf(
                "%s,\"%s\",%s,%s,%s\n",
                $todo['id'],
                str_replace('"', '""', $todo['text']),
                $todo['completed'] ? 'Completed' : 'Pending',
                $todo['created_at'],
                $todo['updated_at']
            );
        }
        exit;
}

function renderTodoListFrame($storage) {
    $todos = $storage->getTodos();
    $stats = $storage->getStats();
    
    echo '<turbo-frame id="todo-list">';
    
    if ($stats['total'] > 0) {
        echo "<div class='stats-header' data-controller='stats'>
                <div data-stats-target='display'>
                    📊 Toplam: {$stats['total']} | ✅ Tamamlanan: {$stats['completed']} | ⏳ Bekleyen: {$stats['pending']}
                </div>";
        
        if ($stats['completed'] > 0) {
            echo "<button class='btn btn-sm btn-danger' 
                         data-action='click->stats#clearCompleted'
                         data-turbo-frame='todo-list'
                         onclick=\"location.href='?action=clear_completed'\">
                    🧹 Tamamlananları Temizle ({$stats['completed']})
                  </button>";
        }
        echo "</div>";
    }
    
    if (empty($todos)) {
        echo '<div class="empty-state" data-controller="celebration">
                <h3>🎉 Harika!</h3>
                <p>Henüz görev yok. Yukarıdan yeni görev ekleyebilirsin.</p>
                <button class="btn btn-sm btn-primary" data-action="click->celebration#animate">
                    🎊 Kutla!
                </button>
              </div>';
    } else {
        foreach ($todos as $todo) {
            renderTodoItem($todo);
        }
    }
    
    echo '</turbo-frame>';
}

function renderTodoItem($todo) {
    $completedClass = $todo['completed'] ? 'completed' : '';
    $toggleText = $todo['completed'] ? '↩️ Geri Al' : '✅ Tamamla';
    $toggleClass = $todo['completed'] ? 'btn-warning' : 'btn-success';
    $timeAgo = timeAgo($todo['created_at']);
    
    echo "<div class='todo-item {$completedClass}' 
              data-controller='todo-item' 
              data-todo-id='{$todo['id']}'>
            <div class='todo-content' data-action='dblclick->todo-item#edit'>
                <div class='todo-text {$completedClass}'>{$todo['text']}</div>
                <div class='todo-meta'>{$timeAgo}</div>
            </div>
            <div class='todo-actions'>
                <a href='?action=toggle&id={$todo['id']}' 
                   class='btn {$toggleClass}'
                   data-turbo-frame='todo-list'
                   data-turbo-method='get'
                   data-action='click->todo-item#beforeToggle'>
                    {$toggleText}
                </a>
                <a href='?action=delete&id={$todo['id']}' 
                   class='btn btn-danger'
                   data-turbo-frame='todo-list'
                   data-action='click->todo-item#beforeDelete'
                   data-turbo-confirm='Bu görevi silmek istediğinizden emin misiniz?'>
                    🗑️ Sil
                </a>
            </div>
          </div>";
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'az önce';
    if ($time < 3600) return floor($time/60) . ' dakika önce';
    if ($time < 86400) return floor($time/3600) . ' saat önce';
    if ($time < 2592000) return floor($time/86400) . ' gün önce';
    
    return date('d.m.Y', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Todo App - JSON Depolama</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" type="module"></script>
    <meta name="description" content="JSON dosya depolama ile todo uygulaması">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Todo App</h1>
            <p>JSON dosya depolama ile</p>
            
            <div class="header-actions">
                <a href="?action=backup" class="btn btn-sm btn-primary">💾 Yedekle</a>
                <a href="?action=export" class="btn btn-sm btn-primary">📊 CSV İndir</a>
                <?php 
                $stats = $storage->getStats();
                if ($stats['total'] > 0) {
                    echo "<span class='stats-badge'>{$stats['total']} görev</span>";
                }
                ?>
            </div>
        </div>
        
        <div class="add-form" data-controller="keyboard">
            <form action="?action=add" method="POST" 
                  data-controller="form" 
                  data-action="submit->form#submit keydown.ctrl+enter@window->form#submitViaKeyboard"
                  data-turbo-frame="todo-list">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="text" 
                        class="form-input" 
                        placeholder="Yeni görev ekle... (Ctrl+Enter)" 
                        required
                        maxlength="100"
                        data-form-target="input"
                        data-action="keyup->form#updateCounter input->form#updateCounter"
                    >
                    <button type="submit" class="btn btn-primary" data-form-target="submit">
                        Ekle
                    </button>
                </div>
                <div class="char-counter" data-form-target="counter">0/100 karakter</div>
            </form>
        </div>
        
        <div class="todo-list" data-controller="loading">
            <?php renderTodoListFrame($storage); ?>
        </div>
        
        <div class="app-footer">
            <p>💾 <strong>Depolama:</strong> JSON dosyası (<?= basename($storage->dataFile ?? 'todos.json') ?>)</p>
            <p>💡 <strong>İpucu:</strong> Çift tıklayarak düzenle | Ctrl+Enter ile hızlı ekle</p>
            <p>📁 <strong>Veri:</strong> Dosya tabanlı, MySQL gerektirmez</p>
        </div>
    </div>
</body>
</html>
