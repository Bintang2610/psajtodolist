<?php
$koneksi =mysqli_connect("localhost", "root", "", "ukk2025_todolist");

if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        mysqli_query($koneksi, "INSERT INTO task VALUES ('', '$task', '$priority', '$due_date', '0')");

        echo "<script>alert('Task berhasil ditambahkan')</script>";
    } else {
        echo "<script>alert('Task gagal ditambahkan')</script>";
        header("location: index.php");
    }
}

// task selesai
if (isset($_GET['complete'])) {
    $id = $_GET['complete'];
    mysqli_query($koneksi, "UPDATE task SET status = '1' WHERE id = '$id'");
    echo "<script>alert('Task berhasil diselesaikan')</script>";
    header("location: index.php");
}

//hapus task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM task  WHERE id = '$id'");
    echo "<script>alert('Task berhasil dihapus')</script>";
    header("location: index.php");
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
        }

        .table-container {
            max-height: 415px; 
            overflow-y: auto;
            padding: 5px;
        }

        /* Form tambah task awalnya tersembunyi */
        .task-form {
            flex-basis: 0px;
            overflow: hidden;
            max-width: 300px;
            transition: flex-basis 0.3s ease-in-out;
        }
        #showFormBtn {
            background-color: white; 
        }

        #showFormBtn:hover {
            background-color: #e0e0e0; 
        }

        #showFormBtn i {
            margin-right: 8px; 
        }
    </style>
  </head>
  <body>
    <div class="container mt-5">
        <h2 class="text-left mb-3">Aplikasi To Do List</h2>

        <hr>

        <div class="task-container">
            <!-- List Task -->
            <div class="task-list">
            <button class="btn mb-2" id="showFormBtn">
                <i class="fas fa-plus"></i>Tambah Task 
            </button>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Task</th>
                                <th>Priority</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0) {
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)) { 
                            ?>
                            <tr>
                                <td><?php echo $no++ ?></td>
                                <td><?php echo $row['task'] ?></td>
                                <td><?php
                                if ($row['priority'] == 1) {
                                    echo "Low";
                                } elseif ($row['priority'] == 2) {
                                    echo "Medium";
                                } else {
                                    echo "High";
                                }
                                ?></td>
                                <td><?php echo $row['due_date']?></td>
                                <td><?php
                                if ($row['status'] == 0) {
                                    echo "<span style='color: red;'>Belum Selesai</span>";
                                } else {
                                    echo "<span style='color: green;'>Selesai</span>";
                                }
                                ?></td>
                                <td>
                                    <?php if ($row['status'] == 0) { ?>
                                        <a href="?complete=<?php echo $row['id'] ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Selesai</a>
                                        <a href="?delete=<?php echo $row['id'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</a>
                                    <?php } ?>
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

            <!-- Form Tambah Task -->
            <div id="taskForm" class="task-form">
                <form action="" method="post" class="border rounded bg-light p-3">
                    <h4>Tambah Task</h4>
                    <label class="form-label">Nama Task</label>
                    <textarea name="task" class="form-control" placeholder="Masukan Task Baru" required rows="3"></textarea>

                    <label class="form-label">Prioritas</label>
                    <select name="priority" class="form-control" required>
                        <option value="">--Pilih Prioritas--</option>
                        <option value="1">Low</option>
                        <option value="2">Medium</option>
                        <option value="3">High</option>
                    </select>

                    <label class="form-label">Tanggal</label>
                    <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d') ?>" required>

                    <button class="btn btn-success w-100 mt-2" name="add_task">Tambah</button>
                    <button type="button" class="btn btn-secondary w-100 mt-2" id="closeFormBtn">Tutup</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.getElementById("showFormBtn").addEventListener("click", function() {
            document.getElementById("taskForm").style.flexBasis = "300px"; // Menampilkan form
        });

        document.getElementById("closeFormBtn").addEventListener("click", function() {
            document.getElementById("taskForm").style.flexBasis = "0px"; // Menyembunyikan form
        });
    </script>
  </body>
</html>