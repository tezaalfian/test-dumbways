<?php
session_start();
class Blog
{
    private $conn;
    public function __construct()
    {
        $this->conn = mysqli_connect("localhost", "root", "", "dbblog");
        if (!$this->conn) {
            var_dump($this->conn);
            die;
        }
    }

    public function query($sql)
    {
        return mysqli_query($this->conn, $sql);
    }

    public function getUser($id)
    {
        $sql = "SELECT * FROM user WHERE email = '" . $id . "'";
        return mysqli_fetch_assoc($this->query($sql));
    }

    public function getBlog($id = null)
    {
        if (!is_null($id)) {
            $sql = "SELECT * FROM image_blog WHERE id = '" . $id . "'";
            return mysqli_fetch_assoc($this->query($sql));
        } else {
            $sql  = "SELECT image_blog.*,user.name, user.id as id_user 
                    FROM image_blog
                    INNER JOIN user ON image_blog.user_id=user.id";
            $data = $this->query($sql);
            $result = [];
            while ($row = mysqli_fetch_assoc($data)) {
                $result[] = $row;
            }
            return $result;
        }
    }
}

class Flasher
{
    public static function setFlash($pesan, $aksi, $tipe)
    {
        $_SESSION["flash"] = [
            "pesan" => $pesan,
            "aksi" => $aksi,
            "tipe" => $tipe
        ];
    }
    public static function flash()
    {
        if (isset($_SESSION["flash"])) {
            echo "
                <div class='alert alert-" . $_SESSION["flash"]["tipe"] . " alert-dismissible fade show' role='alert'>
                    Data <strong>" . $_SESSION["flash"]["pesan"] . "</strong> " . $_SESSION["flash"]["aksi"] . "
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>×</span>
                    </button>
                </div>";
            unset($_SESSION["flash"]);
        }
    }
}

$blog = new Blog();
$blogData = null;
if (count($_POST) > 0) {
    if ($_POST['status'] == "add_user") {
        $data = $blog->getUser($_POST['email']);
        if (is_null($data)) {
            $id = time();
            $sql = "INSERT INTO user(id,name,email) VALUES('$id','" . $_POST['nama'] . "','" . $_POST['email'] . "')";
            $result = $blog->query($sql);
            if ($result) {
                Flasher::setFlash("berhasil", "ditambahkan", "success");
                header("Refresh:0");
            } else {
                Flasher::setFlash("gagal", "ditambahkan", "danger");
                header("Refresh:0");
            }
        } else {
            Flasher::setFlash("gagal", "Sudah terdaftar", "danger");
        }
    }

    if ($_POST['status'] == 'login') {
        $data = $blog->getUser($_POST['email']);
        if (!is_null($data)) {
            $_SESSION['id_user'] = $data['id'];
            header("Refresh:0");
        } else {
            Flasher::setFlash("gagal", "Tidak terdaftar", "danger");
        }
    }

    if ($_POST['status'] == "edit_blog") {
        $sql = "";
        if (!empty($_FILES['image']["name"])) {
            $name = $_FILES['image']['name'];
            $target_file = basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $image_base64 = base64_encode(file_get_contents($_FILES['image']['tmp_name']));
            $image = 'data:image/' . $imageFileType . ';base64,' . $image_base64;
            $sql = "UPDATE image_blog SET title = '" . $_POST['title'] . "', content = '" . $_POST['content'] . "', file_image = '$image' WHERE id = '" . $_POST['id'] . "'";
        } else {
            $sql = "UPDATE image_blog SET title = '" . $_POST['title'] . "', content = '" . $_POST['content'] . "' WHERE id = '" . $_POST['id'] . "'";
        }
        $result = $blog->query($sql);
        if ($result) {
            Flasher::setFlash("berhasil", "disimpan", "success");
            header("Refresh:0");
        } else {
            Flasher::setFlash("gagal", "disimpan", "danger");
            header("Refresh:0");
        }
    }

    if ($_POST['status'] == "add_blog") {
        if (!empty($_FILES['image']["name"])) {
            $name = $_FILES['image']['name'];
            $target_file = basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $image_base64 = base64_encode(file_get_contents($_FILES['image']['tmp_name']));
            $image = 'data:image/' . $imageFileType . ';base64,' . $image_base64;

            $sql = "INSERT INTO image_blog(title,content,file_image,user_id) VALUES('" . $_POST['title'] . "','" . $_POST['content'] . "','$image','" . $_SESSION['id_user'] . "')";
            $result = $blog->query($sql);
            if ($result) {
                Flasher::setFlash("berhasil", "ditambahkan", "success");
                header("Refresh:0");
            } else {
                Flasher::setFlash("gagal", "ditambahkan", "danger");
                header("Refresh:0");
            }
        } else {
            Flasher::setFlash("gagal", "tidak boleh kosong!", "danger");
        }
    }

    if ($_POST['status'] == "delete_blog") {
        $sql = "DELETE FROM image_blog WHERE id = '" . $_POST['blog_id'] . "'";
        $data = $blog->query($sql);
        if ($data) {
            Flasher::setFlash("berhasil", "dihapus", "success");
            header("Refresh:0");
        } else {
            Flasher::setFlash("gagal", "dihapus", "danger");
            header("Refresh:0");
        }
    }

    if ($_POST['status'] == "get_blog") {
        echo json_encode($blog->getBlog($_POST['id']));
        die;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <title>Blog Image</title>
</head>

<body>
    <!-- As a link -->
    <nav class="navbar navbar-light bg-dark">
        <div class="container">
            <a class="navbar-brand text-white" href="#">Blog Image</a>
            <?php if (!isset($_SESSION['id_user'])) : ?>
                <button data-toggle="modal" data-target="#modalLogin" type="button" class="btn btn-outline-primary float-right">Login</button>
            <?php endif; ?>
        </div>
    </nav>
    <!-- AFTER LOGIN -->
    <div class="container">
        <div class="row">
            <div class="col-md-6 my-5">
                <h1>Daftar Blog Image</h1>
            </div>
            <?php if (isset($_SESSION['id_user'])) : ?>
                <div class="col-md-6 my-5 d-flex justify-content-end align-items-center">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalBlog">Add Blog Image</button>
                    &nbsp;
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalUser">Add User</button>
                </div>
            <?php endif; ?>
        </div>
        <?php Flasher::flash(); ?>
        <div class="row">
            <?php foreach ($blog->getBlog() as $key) : ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <img src="<?= $key['file_image']; ?>" class="card-img-top" alt="<?= $key['title']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $key['title']; ?></h5>
                            <p class="card-text"><?= $key['content']; ?></p>
                            <?php if (isset($_SESSION['id_user'])) : ?>
                                <?php if ($_SESSION['id_user'] == $key['id_user']) : ?>
                                    <button data-id="<?= $key['id']; ?>" class="btn btn-success btn-block mb-1" id="btn-edit-blog" data-toggle="modal" data-target="#modalEditBlog">Edit</button>
                                    <form action="<?= '4.php' ?>" method="post">
                                        <input type="hidden" name="status" value="delete_blog">
                                        <input type="hidden" name="blog_id" value="<?= $key['id']; ?>">
                                        <button class="btn btn-danger btn-block" id="btn-delete-blog">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-muted">
                            Creator : <?= ucwords($key['name']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="modalBlog" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Blog Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= '4.php' ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="status" value="add_blog">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="nama" class="form-control" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="image" accept="image/png, image/jpg, image/jpeg" required>
                                <label class="custom-file-label" for="customFile">Choose file</label>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit blog image -->
    <div class="modal fade" id="modalEditBlog" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Blog Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= '4.php' ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="status" value="edit_blog">
                        <input type="hidden" name="id" id="id_blog">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="nama" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" class="form-control" id="content" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="image" accept="image/png, image/jpg, image/jpeg">
                                <label class="custom-file-label" for="customFile">Choose file</label>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- modal login -->
    <div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Masukkan Email</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= '4.php' ?>" method="post">
                        <input type="hidden" name="status" value="login">
                        <div class="form-group">
                            <label>Masukkan Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- modal add user -->
    <div class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Tambah User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= '4.php' ?>" method="post">
                        <input type="hidden" name="status" value="add_user">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="nama" class="form-control" name="nama" required>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END AFTER LOGIN -->
    <!-- LOGIN -->

    <!-- END LOGIN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).on('click', '#btn-delete-blog', function(e) {
            if (!confirm("Are you serius ?")) {
                e.preventDefault();
            }
        });
        $(document).on("click", '#btn-edit-blog', function() {
            const id = $(this).data("id");
            const data = {
                status: "get_blog",
                id: id
            }
            $.ajax({
                url: "4.php",
                type: "post",
                dataType: "json",
                data: data,
                success: function(result) {
                    $('#title').val(result.title);
                    $("#content").text(result.content);
                    $("#id_blog").val(result.id);
                }
            });
        });
    </script>
</body>

</html>