<?php
namespace application\models;

use ItForFree\SimpleMVC\User;
/**
 * Класс для проверки авторизационных данных пользователя
 */
class AuthUser extends User
{
    
    
    /**
     * Проверка логина и пароля пользователя.
     */
    protected function checkAuthData($login, $pass): bool {
	$result = false;
	$User = new UserModel();
	$siteAuthData = $User->getAuthData($login);
        if (isset($siteAuthData['pass'])) {
	    $pass .= $siteAuthData['salt'];
	    $passForCheck = password_verify($pass, $siteAuthData['pass']);
	    if ($passForCheck) {
		$result = true;
		// Сбросить счетчик неудачных попыток входа при успешной аутентификации
		$User->resetLoginAttempts($login);
	    }
	}
        return $result;
    }

    /**
     * Получить роль по имени пользователя
     */
    protected function getRoleByUserName($login): string {
	$User = new UserModel();
	$siteAuthData = $User->getRole($login);
	if (isset($siteAuthData['role'])) {
	    return $siteAuthData['role'];
        }
    }
    
    /**
     * Присваивает данной сессии имя пользователя
     * и роль в соответствии с полученными данными
     *
     * @param srting $login имя пользователя
     * @param string $pass  пароль
     */
    public function login(string $login, string $pass): bool
    {
        $User = new UserModel();
        
        if ($this->checkAuthData($login, $pass)) {
            
            $role = $this->getRoleByUserName($login);
            $this->role =  $role;
            $this->userName = $login;
            $this->Session->session['user']['role'] = $role;
            $this->Session->session['user']['userName'] = $login;
            return true;
        } else {
            // Увеличить счетчик неудачных попыток входа при неудачной аутентификации
            $User->incrementLoginAttempts($login);
            return false;
        }
    }
}
