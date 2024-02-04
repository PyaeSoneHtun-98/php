<?php

namespace Libs\Database;

use PDOException;

class UsersTable
{
    private $db;

    public function __construct(MySQL $mysql)
    {
        $this->db = $mysql->connect();
    }

    public function getAll()
    {
        $result = $this->db->query(
            "SELECT users.*, roles.name AS role
            FROM users LEFT JOIN roles
            ON users.role_id = roles.id"
            );
        return $result->fetchAll();
    }

    public function insert($data)
    {
        try {
            $statement = $this->db->prepare(
                "INSERT INTO users (name, email, phone, address,
                password, created_at) VALUES (:name, :email,
                :phone, :address, :password, NOW())"
            );
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            $statement->execute($data);

            return $this->db->lastInsertId();

        }catch (PDOException $e){
            echo $e->getMessage();
            exit();
        }
    }

    public function findByEmailAndPassword($email, $password)
    {
        $statement = $this->db->prepare("SELECT * FROM users WHERE email=:email");
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();
        if($user) {
            if(password_verify($password, $user->password)) {
                return $user;
            }
        }
        return false;
    }

    public function updatePhoto($id, $photo)
    {
        try {
            $statement = $this->db->prepare("UPDATE users SET photo=:photo WHERE id = :id");
            $statement->execute(['id' => $id, 'photo' => $photo]);

            return $statement->rowCount();

        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function delete($id)
    {
        $statement = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $statement->execute(['id' => $id]);

        return $statement->rowCount();
    }

    public function suspend($id)
    {
        $statement = $this->db->prepare("UPDATE users SET suspended=1 WHERE id= :id");
        $statement->execute(['id' => $id]);

        return $statement->rowCount();
    }

    public function unsuspend($id)
    {
        $statement = $this->db->prepare("UPDATE users SET suspended=0 WHERE id= :id");
        $statement->execute(['id' => $id]);

        return $statement->rowCount();
    }

    public function changeRole($id, $role) 
    {
        $statement = $this->db->prepare("UPDATE users SET role_id=:role WHERE id=:id");
        $statement->execute(['id' => $id, 'role' => $role]);
        return $statement->rowCount();
    }
}