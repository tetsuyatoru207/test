<?php

class UsersModel extends Database {

    public function checkUser($uid, $email) {
        $sql = "SELECT ACCOUNT_ID
                FROM Account
                WHERE ACCOUNT_USERNAME = ?
                   OR ACCOUNT_EMAIL = ?";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$uid, $email]);

        return $stmt->rowCount() == 0;
    }

    public function setUser($uid, $pwd, $email) {
        $sql = "INSERT INTO Account (
                    ACCOUNT_FULLNAME,
                    ACCOUNT_USERNAME,
                    ACCOUNT_EMAIL,
                    ACCOUNT_PASSWORD,
                    ACCOUNT_ROLE,
                    ACCOUNT_STATUS
                ) VALUES (?, ?, ?, ?, 'User', 'Active')";

        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
        $stmt = $this->connect()->prepare($sql);

        return $stmt->execute([
            $uid,
            $uid,
            $email,
            $hashedPwd
        ]);
    }

    public function getUser($userInput) {
        $sql = "SELECT
                    ACCOUNT_ID AS users_id,
                    ACCOUNT_USERNAME AS users_uid,
                    ACCOUNT_EMAIL AS users_email,
                    ACCOUNT_PASSWORD AS users_pwd,
                    ACCOUNT_ROLE AS users_role
                FROM Account
                WHERE ACCOUNT_USERNAME = ?
                   OR ACCOUNT_EMAIL = ?";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$userInput, $userInput]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
