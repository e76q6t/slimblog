<?php
namespace app\controllers\admin;

class AuthController extends \SlimController\SlimController
{
    public function loginAction()
    {
        if ($this->app->request->isPost()) {
            $username = $this->app->request->post('username');
            $password = $this->app->request->post('password');
            if ($this->app->auth->login($username, $password)) {
                $this->app->flash('success', 'ログインしました。');
                if ($from = $this->app->request->get('from')) {
                    return $this->app->redirect($from);
                }
                return $this->app->redirect('/');
            }
        }

        $from = $this->app->request->get('from');
        $action = '/admin/login';
        $action .= $from ? '?from=' . $from : '';
        return $this->app->render('admin/login.html', ['action' => $action]);
    }

    public function logoutAction()
    {
        $this->app->auth->logout();
        $this->app->flash('success', 'ログアウトしました。');
        return $this->app->redirect('/');
    }
}
