<?php
// ============================================================
// server/prosesUser.php — Kelola User (super_admin only)
// aksi: tambah | edit | hapus
// ============================================================
session_start();
include 'koneksi.php';
include 'auth.php';
requireRole('super_admin');

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';
function redirUser($t,$k){ header("Location: ../users.php?$t=$k"); exit(); }

if ($aksi === 'tambah') {
    $nama=$_POST['nama']??''; $uname=$_POST['username']??'';
    $pass=$_POST['password']??''; $role=$_POST['role']??'user';
    $divisi=$role==='super_admin'?null:($_POST['divisi']??'');
    if(!$nama||!$uname||!$pass){ redirUser('error','invalid'); }
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $s=mysqli_prepare($koneksi,"INSERT INTO users(nama,username,password,role,divisi) VALUES(?,?,?,?,?)");
    mysqli_stmt_bind_param($s,'sssss',$nama,$uname,$hash,$role,$divisi);
    mysqli_stmt_execute($s) ? redirUser('success','added') : redirUser('error','duplicate');
}

if ($aksi === 'edit') {
    $id=intval($_POST['id']??0); $nama=$_POST['nama']??'';
    $role=$_POST['role']??''; $divisi=$role==='super_admin'?null:($_POST['divisi']??'');
    $pass=$_POST['password']??''; $aktif=intval($_POST['is_active']??1);
    if(!$id||!$nama||!$role){ redirUser('error','invalid'); }
    if($pass){
        $hash=password_hash($pass,PASSWORD_DEFAULT);
        $s=mysqli_prepare($koneksi,"UPDATE users SET nama=?,role=?,divisi=?,password=?,is_active=? WHERE id=?");
        mysqli_stmt_bind_param($s,'ssssii',$nama,$role,$divisi,$hash,$aktif,$id);
    } else {
        $s=mysqli_prepare($koneksi,"UPDATE users SET nama=?,role=?,divisi=?,is_active=? WHERE id=?");
        mysqli_stmt_bind_param($s,'sssii',$nama,$role,$divisi,$aktif,$id);
    }
    mysqli_stmt_execute($s); redirUser('success','updated');
}

if ($aksi === 'hapus') {
    $id=intval($_GET['id']??0);
    if($id===$_SESSION['user_id']){ redirUser('error','self'); }
    mysqli_query($koneksi,"DELETE FROM users WHERE id=$id");
    redirUser('success','deleted');
}
header('Location: ../users.php'); exit();
