<?php
$koneksi =mysqli_connect("localhost", "root", "", "ukk2025_todolist");

if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    $due_date = mysqli_real_escape_string($koneksi, $_POST['due_date']);
    $today = date('Y-m-d');

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        mysqli_query($koneksi, "INSERT INTO task VALUES ('', '$task', '$priority', '$due_date', '0', '$description')");

        echo "<script>alert('Task berhasil ditambahkan'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Task gagal ditambahkan')</script>";
        header("location: index.php");
    }
}

// task selesai
if (isset($_GET['complete'])) {
    $id = $_GET['complete'];

    // Ambil status saat ini
    $query = mysqli_query($koneksi, "SELECT status FROM task WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query);
    
    // Toggle status (jika 1 jadi 0, jika 0 jadi 1)
    $new_status = ($data['status'] == 1) ? 0 : 1; 

    // Update status di database
    mysqli_query($koneksi, "UPDATE task SET status = '$new_status' WHERE id = '$id'");

    // Notifikasi
    if ($new_status == 1) {
        echo "<script>alert('Task berhasil diselesaikan')</script>";
    } else {
        echo "<script>alert('Task dikembalikan ke status belum selesai')</script>";
    }

    // Refresh halaman
    header("location: index.php");
}


//hapus task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM task  WHERE id = '$id'");
    echo "<script>alert('Task berhasil dihapus')</script>";
    header("location: index.php");
}

if (isset($_GET['id'])) {
    $task_id = mysqli_real_escape_string($koneksi, $_GET['id']);

    $query = "SELECT id, task_name, priority, due_date, description FROM task WHERE id='$task_id'";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);

    // Konversi tanggal dari Y-m-d ke d-m-Y
    if ($data) {
        $data['due_date'] = date('d-m-Y', strtotime($data['due_date']));
    }

    echo json_encode($data);
}

if (isset($_POST['edit_task'])) {
    $task_id = mysqli_real_escape_string($koneksi, $_POST['task_id']);
    $task = mysqli_real_escape_string($koneksi, $_POST['task']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);
    $priority = mysqli_real_escape_string($koneksi, $_POST['priority']);
    $due_date = mysqli_real_escape_string($koneksi, $_POST['due_date']);

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        $query = "UPDATE task SET task='$task', priority='$priority', due_date='$due_date', description='$description' 
                  WHERE id='$task_id'";
        $result = mysqli_query($koneksi, $query);

        if ($result) {
            echo "<script>
                    alert('Task berhasil diperbarui');
                    window.location.href='index.php';
                  </script>";
        } else {
            echo "<script>alert('Gagal memperbarui task');</script>";
        }
    } else {
        echo "<script>alert('Pastikan semua kolom terisi');</script>";
    }
}

// Ambil parameter search dari URL
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : "";

// Query dasar
$query = "SELECT * FROM task WHERE 1";

// Jika search tidak kosong, cari berdasarkan ID atau task
if (!empty($search)) {
    $query .= " AND (id = '$search' OR task LIKE '%$search%')";
}

// Jalankan query
$result = mysqli_query($koneksi, $query);


    $priority = isset($_GET['priority']) ? mysqli_real_escape_string($koneksi, $_GET['priority']) : "";
    $status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : "";

    // Query dasar
    $query = "SELECT * FROM task WHERE 1";

    // Tambahkan filter Prioritas (jika ada)
    if (!empty($priority)) {
        $query .= " AND priority = '$priority'";
    }

    // Tambahkan filter Status (jika ada)
    if ($status !== "") {
        $query .= " AND status = '$status'";
    }


$result = mysqli_query($koneksi, "SELECT * FROM task ORDER BY status ASC, priority DESC, due_date ASC");
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi Todo List | UKK RPL 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet" />
    <style>
        .task-container {
            display: flex;
            gap: 20px;
        }

        .task-list {
            flex: 1;
            max-height: 435px;
        }

        .table-container {
            max-height: 330px; 
            overflow-y: auto;
            padding: 5px;
        }

        .task-form {
            flex-basis: 0px;
            overflow: hidden;
            max-width: 350px;
            transition: flex-basis 0.3s ease-in-out;
        }

        .task-detail {
            flex-basis: 0px;
            overflow: hidden;
            max-width: 350px;
            transition: flex-basis 0.3s ease-in-out;
        }

        .task-edit {
            flex-basis: 0px;
            overflow: hidden;
            max-width: 350px;
            transition: flex-basis 0.3s ease-in-out;
        }

        #showFormBtn i {
            margin-right: 8px; 
        }

        .hidden-column {
            display: none;
        }
    </style>
  </head>
  <body class="bg-body-tertiary">
    <div>
        <nav class="navbar shadow-sm navbar-expand-lg bg-white sticky-top">
            <div class="container-fluid p-2 px-5">
                <a class="navbar-brand fw-bold" href="#">Notes App</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                </div>
            </div>
        </nav>
        <div class="container-fluid p-3 px-5 d-flex align-items-center gap-3">
        <div class="input-group rounded-pill border overflow-hidden" style="max-width: 300px;">
            <span class="input-group-text bg-white border-0">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control border-0" placeholder="Cari Task..." id="searchInput">
        </div>

                <button class="btn btn-outline-secondary rounded-pill" id="showFormBtn">
                    <i class="fas fa-plus"></i>Tambah Task 
                </button>
        </div>
        <div class="px-5">
            <div class="task-container">
                <!-- List Task -->
                <div class="task-list shadow-sm bg-white p-4 rounded">
                    <div class="px-2 d-flex justify-content-between align-items-center">
                        <h5>To Do List</h5>
                        <div class="d-flex gap-3">
                            <!-- Filter Priority -->
                            <div>
            <select id="filterPriority" class="form-select">
                <option value="">Prioritas</option>
                <option value="1">Low</option>
                <option value="2">Medium</option>
                <option value="3">High</option>
            </select>
        </div>

        <!-- Filter Status -->
        <div>
            <select id="filterStatus" class="form-select">
                <option value="">Status</option>
                <option value="1">Selesai</option>
                <option value="0">Belum Selesai</option>
            </select>
        </div>
                        </div>

                    </div>
                <hr>
                <div class="table-container">
                <table class="table rounded">
    <tbody id="taskTableBody">
        <?php 
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) { 
        ?>
        <tr id="task-<?php echo $row['id']; ?>" 
            data-task="<?php echo htmlspecialchars($row['task']); ?>"
            data-priority="<?php echo $row['priority']; ?>"
            data-status="<?php echo $row['status']; ?>"
            class="border-b cursor-pointer hover:bg-gray-100">
            
            <td>
                <input type="checkbox" onclick="completeTask(<?php echo $row['id']; ?>)" 
                    <?php echo ($row['status'] == 1) ? 'checked' : ''; ?>>
            </td>
            <td><?php echo $row['task']; ?></td>
            <td class="toggle-column-3">
                <?php 
                echo ($row['priority'] == 1) ? "Low" : (($row['priority'] == 2) ? "Medium" : "High"); 
                ?>
            </td>
            <td><?php echo $row['due_date']; ?></td>
            <td>
                <?php 
                echo ($row['status'] == 0) ? "<span style='color: red;'>Belum Selesai</span>" : "<span style='color: green;'>Selesai</span>"; 
                ?>
            </td>
            <td class="hidden-desc" style="display: none;"><?php echo htmlspecialchars($row['description']); ?></td>
            <td class="toggle-column-6">
                <button class="btn btn-primary btn-sm showEditBtn" data-id="<?php echo $row['id']; ?>">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-warning btn-sm showDetailBtn" data-id="<?php echo $row['id']; ?>">
                    <i class="fas fa-eye"></i>
                </button>                                   
                <a href="?delete=<?php echo $row['id'] ?>" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        </tr>
        <?php 
            }
        }
        ?>
    </tbody>
</table>
                </div>
            </div>

            <!-- Form Edit Task -->
<div id="taskEditForm" class="task-edit shadow-sm">
    <form action="" method="post" class="rounded bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="m-0 align-middle">Edit Task</h5>
    <div class="d-flex align-items-center gap-2">
        <p id="editStatus" class="text-base m-0 align-middle">Status</p>
        <button type="button" id="closeEditBtn" class="border-0 text-black bg-white fs-4 d-flex align-items-center justify-content-center">
            &times;
        </button>
    </div>
</div>


        <!-- Hidden input untuk menyimpan ID task -->
        <input type="hidden" id="editTaskId" name="task_id">

        <label class="form-label">Nama Task</label>
        <input id="editTaskName" name="task" class="form-control" placeholder="Masukan Task Baru" required>

        <label class="form-label mt-1">Deskripsi Task</label>
        <textarea id="editDesc" name="description" class="form-control" placeholder="Masukan Deskripsi Baru" required rows="3"></textarea>

        <div class="row mt-2">
            <div class="col-md-6">
                <label class="form-label">Prioritas</label>
                <select id="editPriority" name="priority" class="form-control" required>
                    <option value="">--Pilih Prioritas--</option>
                    <option value="1">Low</option>
                    <option value="2">Medium</option>
                    <option value="3">High</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Tenggat Waktu</label>
                <input type="date" id="editDueDate" name="due_date" class="form-control" 
       value="<?php echo isset($row['due_date']) ? $row['due_date'] : ''; ?>" required>
            </div>
        </div>

        <button class="btn btn-primary w-100 mt-4 mb-1" name="edit_task">Simpan Perubahan</button>
    </form>
</div>



            <!-- Panel Detail Task -->
            <div id="taskDetail" class="task-detail shadow-sm">
                <div class="rounded bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 id="detailTask" class="m-0">Detail Task</h5>
                    <button type="button" id="closeDetailBtn" class="border-0 text-black bg-white fs-4">
                        &times;
                    </button>
                    </div>

                    <hr>

                    <div>
                        <p id="detailDesc" class="text-base"></p>

                        <p class="text-gray-500 mt-3">Prioritas:</p>
                        <p id="detailPriority" class="font-semibold"></p>

                        <p class="text-gray-500 mt-3">Tenggat Waktu:</p>
                        <p id="detailDueDate" class="text-base"></p>

                        <p class="text-gray-500 mt-3">Status:</p>
                        <p id="detailStatus" class="text-base"></p>
                    </div>
                </div>
            </div>

            <!-- Form Tambah Task -->
            <div id="taskForm" class="task-form shadow-sm">
                <form action="" method="post" class="rounded bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">Tambah Task</h5>
                    <button type="button" id="closeFormBtn" class="border-0 text-black bg-white fs-4">
                        &times;
                    </button>
                </div>
                    <label class="form-label">Nama Task</label>
                    <input name="task" class="form-control" placeholder="Masukan Task Baru" required >

                    <label class="form-label mt-1">Deskripsi Task</label>
                    <textarea name="description" class="form-control" placeholder="Masukan Deskripsi Baru" required rows="3"></textarea>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Prioritas</label>
                            <select name="priority" class="form-control" required>
                                <option value="">--Pilih Prioritas--</option>
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tenggat Waktu</label>
                            <input type="date" name="due_date" class="form-control"
                                value="<?php echo date('Y-m-d'); ?>" 
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                        <button class="btn btn-success w-100 mt-4 mb-1" name="add_task">Tambah</button>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.getElementById("showFormBtn").addEventListener("click", function() {
            document.getElementById("taskForm").style.flexBasis = "350px"; // Menampilkan form
        });

        document.getElementById("closeFormBtn").addEventListener("click", function() {
            document.getElementById("taskForm").style.flexBasis = "0px"; // Menyembunyikan form
        });

        function completeTask(taskId) {
            window.location.href = "?complete=" + taskId;
        }

        document.querySelectorAll(".showDetailBtn").forEach(button => {
    button.addEventListener("click", function () {
        let taskId = this.getAttribute("data-id"); // Ambil ID task
        let row = document.getElementById("task-" + taskId); // Ambil baris berdasarkan ID
        
        // Ambil data dari kolom tabel
        let task = row.cells[1].innerText;
        let priority = row.cells[2].innerText;
        let dueDate = row.cells[3].innerText;
        let status = row.cells[4].innerHTML; // Ambil innerHTML untuk mempertahankan warna status
        let description = row.cells[5].innerText; // Ambil deskripsi dari kolom tersembunyi (index ke-5)

        // Masukkan data ke dalam panel taskDetail
        document.getElementById("detailTask").innerText = task;
        document.getElementById("detailDesc").innerText = description; // Tampilkan deskripsi
        document.getElementById("detailPriority").innerText = priority;
        document.getElementById("detailDueDate").innerText = dueDate;
        document.getElementById("detailStatus").innerHTML = status;

        // Tampilkan panel Task Detail
        document.getElementById("taskDetail").style.flexBasis = "350px";
        toggleColumns(true);
    });
});

        // Event untuk menutup Task Detail
        document.getElementById("closeDetailBtn").addEventListener("click", function() {
            document.getElementById("taskDetail").style.flexBasis = "0px";
            toggleColumns(false);
        });

function toggleColumns(hidden) {
    document.querySelectorAll(".toggle-column-3, .toggle-column-6").forEach(col => {
        col.classList.toggle("hidden-column", hidden);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const priorityFilter = document.getElementById("filterPriority");
    const statusFilter = document.getElementById("filterStatus");
    const searchInput = document.getElementById("searchInput");
    const tableRows = document.querySelectorAll("#taskTableBody tr");

    function filterTable() {
        const selectedPriority = priorityFilter.value;
        const selectedStatus = statusFilter.value;
        const searchText = searchInput.value.trim().toLowerCase(); // Ambil input search

        tableRows.forEach(row => {
            const rowPriority = row.getAttribute("data-priority");
            const rowStatus = row.getAttribute("data-status");
            const rowTask = row.getAttribute("data-task").toLowerCase(); // Ambil task dari atribut

            const matchPriority = selectedPriority === "" || rowPriority === selectedPriority;
            const matchStatus = selectedStatus === "" || rowStatus === selectedStatus;
            const matchSearch = searchText === "" || rowTask.includes(searchText); // Cek apakah task mengandung searchText

            if (matchPriority && matchStatus && matchSearch) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    priorityFilter.addEventListener("change", filterTable);
    statusFilter.addEventListener("change", filterTable);
    searchInput.addEventListener("keyup", filterTable); // Jalankan filter saat mengetik
});

document.querySelectorAll(".showEditBtn").forEach(button => {
    button.addEventListener("click", function () {
        let taskId = this.getAttribute("data-id"); // Ambil ID task
        let row = document.getElementById("task-" + taskId); // Ambil baris berdasarkan ID
        
        // Ambil data dari kolom tabel
        let task = row.cells[1].innerText;
        let priority = row.cells[2].innerText;
        let dueDate = row.cells[3].innerText.trim(); // Pastikan tidak ada spasi ekstra
        let status = row.cells[4].innerHTML;
        let description = row.cells[5].innerText; // Ambil deskripsi dari kolom tersembunyi (index ke-5)

        // Masukkan data ke dalam input form
        document.getElementById("editTaskId").value = taskId;
        document.getElementById("editTaskName").value = task;
        document.getElementById("editDesc").value = description;
        document.getElementById("editStatus").innerHTML = status;
        document.getElementById("editPriority").value = convertPriority(priority);

        let formattedDate = formatDate(dueDate);
        let dateInput = document.getElementById("editDueDate");

        if (formattedDate) {
            dateInput.value = formattedDate; // Pastikan nilai diisi ke dalam input
        } else {
            dateInput.value = ""; // Jika ada kesalahan, kosongkan field
        }

        console.log("Tanggal sebelum format: ", dueDate);
        console.log("Tanggal setelah format: ", formattedDate);

        // Tampilkan form edit
        document.getElementById("taskEditForm").style.flexBasis = "350px";
        toggleColumns(true);
    });
});

// Fungsi untuk mengonversi format tanggal (DD-MM-YYYY → YYYY-MM-DD)
function formatDate(dateText) {
    if (!dateText) return ""; // Jika kosong, langsung return

    let parts = dateText.includes("/") ? dateText.split("/") : dateText.split("-");

    if (parts.length === 3) {
        let day = parts[0].padStart(2, '0');   // Tambahkan nol di depan jika perlu
        let month = parts[1].padStart(2, '0'); // Pastikan bulan juga 2 digit
        let year = parts[2];

        let finalDate = `${year}-${month}-${day}`;
        return finalDate;
    }
    return "";
}


document.getElementById("closeEditBtn").addEventListener("click", function () {
    document.getElementById("taskEditForm").style.flexBasis = "0px";
    toggleColumns(false);
});

// Fungsi untuk mengonversi teks Prioritas menjadi nilai (Low = 1, Medium = 2, High = 3)
function convertPriority(priorityText) {
    if (priorityText.toLowerCase() === "low") return "1";
    if (priorityText.toLowerCase() === "medium") return "2";
    if (priorityText.toLowerCase() === "high") return "3";
    return "";
}

    </script>
  </body>
</html>