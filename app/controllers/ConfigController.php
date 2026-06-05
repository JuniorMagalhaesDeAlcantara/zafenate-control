<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Perfil as PerfilModel;

class ConfigController extends Controller
{
    private Database   $db;
    private PerfilModel $perfilModel;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            redirect('/login');
        }
        $this->db          = Database::getInstance();
        $this->perfilModel = new PerfilModel();
    }

    // ================================================================
    // USUÁRIOS
    // ================================================================

    public function usuarios(): void
    {
        if (!can('configuracoes')) {
            Session::flash(
                'error',
                'Você não possui permissão para acessar este módulo.'
            );

            redirect('/dashboard');
        }

        $usuarios = $this->db->fetchAll("
        SELECT u.*, p.nome AS perfil_nome
        FROM usuarios u
        LEFT JOIN perfis p ON p.id = u.perfil_id
        ORDER BY u.nome ASC
    ");

        $perfis = $this->perfilModel->listar();

        $this->view('config/usuarios', [
            'pageTitle'  => 'Usuários',
            'usuarios'   => $usuarios,
            'perfis'     => $perfis,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Configurações', 'url' => '#'],
                ['label' => 'Usuários', 'url' => '#'],
            ],
        ]);
    }

    public function usuarioCreate(): void
    {
        $perfis = $this->perfilModel->listar();

        $this->view('config/usuario_form', [
            'pageTitle'  => 'Novo Usuário',
            'usuario'    => null,
            'perfis'     => $perfis,
            'breadcrumb' => [
                ['label' => 'Usuários', 'url' => '/config/usuarios'],
                ['label' => 'Novo',     'url' => '#'],
            ],
        ]);
    }

    public function usuarioStore(Request $request): void
    {
        try {
            $nome      = trim($request->input('nome', ''));
            $email     = trim($request->input('email', ''));
            $senha     = $request->input('senha', '');
            $perfilId  = $request->input('perfil_id') ?: null;
            $nivel     = $request->input('nivel', 'operador');

            if (empty($nome) || empty($email) || empty($senha)) {
                throw new \RuntimeException('Nome, e-mail e senha são obrigatórios.');
            }

            if (strlen($senha) < 6) {
                throw new \RuntimeException('A senha deve ter pelo menos 6 caracteres.');
            }

            $existe = $this->db->fetchOne(
                "SELECT id FROM usuarios WHERE email = :email LIMIT 1",
                ['email' => $email]
            );
            if ($existe) {
                throw new \RuntimeException('Já existe um usuário com este e-mail.');
            }

            $this->db->execute("
                INSERT INTO usuarios (nome, email, senha, nivel, perfil_id, ativo)
                VALUES (:nome, :email, :senha, :nivel, :perfil_id, 1)
            ", [
                'nome'      => $nome,
                'email'     => $email,
                'senha'     => password_hash($senha, PASSWORD_BCRYPT),
                'nivel'     => $nivel,
                'perfil_id' => $perfilId,
            ]);

            Session::flash('success', "Usuário \"{$nome}\" criado com sucesso.");
            $this->redirect('/config/usuarios');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/config/usuarios/criar');
        }
    }

    public function usuarioEdit(Request $request, int $id): void
    {
        $usuario = $this->db->fetchOne(
            "SELECT * FROM usuarios WHERE id = :id LIMIT 1",
            ['id' => $id]
        );

        if (!$usuario) {
            Session::flash('error', 'Usuário não encontrado.');
            $this->redirect('/config/usuarios');
            return;
        }

        $perfis = $this->perfilModel->listar();

        $this->view('config/usuario_form', [
            'pageTitle'  => 'Editar Usuário',
            'usuario'    => $usuario,
            'perfis'     => $perfis,
            'breadcrumb' => [
                ['label' => 'Usuários', 'url' => '/config/usuarios'],
                ['label' => 'Editar',   'url' => '#'],
            ],
        ]);
    }

    public function usuarioUpdate(Request $request, int $id): void
    {
        try {
            $nome     = trim($request->input('nome', ''));
            $email    = trim($request->input('email', ''));
            $senha    = $request->input('senha', '');
            $perfilId = $request->input('perfil_id') ?: null;
            $nivel    = $request->input('nivel', 'operador');

            if (empty($nome) || empty($email)) {
                throw new \RuntimeException('Nome e e-mail são obrigatórios.');
            }

            $existe = $this->db->fetchOne(
                "SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1",
                ['email' => $email, 'id' => $id]
            );
            if ($existe) {
                throw new \RuntimeException('Já existe outro usuário com este e-mail.');
            }

            if (!empty($senha)) {
                if (strlen($senha) < 6) {
                    throw new \RuntimeException('A senha deve ter pelo menos 6 caracteres.');
                }
                $this->db->execute("
                    UPDATE usuarios
                    SET nome = :nome, email = :email, senha = :senha,
                        nivel = :nivel, perfil_id = :perfil_id
                    WHERE id = :id
                ", [
                    'nome'      => $nome,
                    'email'     => $email,
                    'senha'     => password_hash($senha, PASSWORD_BCRYPT),
                    'nivel'     => $nivel,
                    'perfil_id' => $perfilId,
                    'id'        => $id,
                ]);
            } else {
                $this->db->execute("
                    UPDATE usuarios
                    SET nome = :nome, email = :email,
                        nivel = :nivel, perfil_id = :perfil_id
                    WHERE id = :id
                ", [
                    'nome'      => $nome,
                    'email'     => $email,
                    'nivel'     => $nivel,
                    'perfil_id' => $perfilId,
                    'id'        => $id,
                ]);
            }

            Session::flash('success', 'Usuário atualizado com sucesso.');
            $this->redirect('/config/usuarios');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect("/config/usuarios/{$id}/editar");
        }
    }

    public function usuarioToggle(Request $request, int $id): void
    {
        // Impede desativar o próprio usuário logado
        if ($id === (int) Session::get('usuario_id')) {
            Session::flash('error', 'Você não pode desativar sua própria conta.');
            $this->redirect('/config/usuarios');
            return;
        }

        $this->db->execute(
            "UPDATE usuarios SET ativo = IF(ativo = 1, 0, 1) WHERE id = :id",
            ['id' => $id]
        );

        Session::flash('success', 'Status do usuário atualizado.');
        $this->redirect('/config/usuarios');
    }

    // ================================================================
    // PERFIS DE ACESSO
    // ================================================================

    public function perfis(): void
    {
        $perfis  = $this->perfilModel->listar();
        $modulos = PerfilModel::MODULOS;

        // Decodifica permissões de cada perfil
        foreach ($perfis as &$p) {
            $p['permissoes'] = json_decode($p['permissoes'] ?? '{}', true) ?: [];
        }
        unset($p);

        $this->view('config/perfis', [
            'pageTitle'  => 'Perfis de Acesso',
            'perfis'     => $perfis,
            'modulos'    => $modulos,
            'breadcrumb' => [
                ['label' => 'Dashboard',    'url' => '/dashboard'],
                ['label' => 'Configurações', 'url' => '#'],
                ['label' => 'Perfis',       'url' => '#'],
            ],
        ]);
    }

    public function perfisSave(Request $request): void
    {
        try {
            $dados = $request->input('perfis', []);

            if (!is_array($dados)) {
                throw new \RuntimeException('Dados inválidos.');
            }

            foreach ($dados as $perfilId => $info) {
                $perfilId = (int) $perfilId;
                if (!$perfilId) continue;

                $nome  = trim($info['nome'] ?? '');
                $perms = $info['permissoes'] ?? [];

                if (empty($nome)) continue;

                // Normaliza: cada módulo pode ser 'true', 'view' ou 'false'
                $permissoes = [];
                foreach (PerfilModel::MODULOS as $mod => $_) {
                    $val = $perms[$mod] ?? 'false';
                    $permissoes[$mod] = in_array($val, ['true', 'view']) ? $val : 'false';
                }

                $this->perfilModel->salvar($perfilId, $nome, $permissoes);
            }

            Session::flash('success', 'Perfis de acesso salvos com sucesso.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/config/perfis');
    }

    // ================================================================
    // EMPRESA
    // ================================================================

    public function empresa(): void
    {
        $empresa = $this->db->fetchOne("SELECT * FROM config_empresa WHERE id = 1 LIMIT 1") ?? [];

        $this->view('config/empresa', [
            'pageTitle'  => 'Dados da Empresa',
            'empresa'    => $empresa,
            'breadcrumb' => [
                ['label' => 'Dashboard',    'url' => '/dashboard'],
                ['label' => 'Configurações', 'url' => '#'],
                ['label' => 'Empresa',      'url' => '#'],
            ],
        ]);
    }

    public function empresaSave(Request $request): void
    {
        try {
            $logo = null;

            // Upload de logo
            if (!empty($_FILES['logo']['tmp_name'])) {
                $file     = $_FILES['logo'];
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed  = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

                if (!in_array($ext, $allowed)) {
                    throw new \RuntimeException('Formato de logo inválido. Use JPG, PNG, SVG ou WEBP.');
                }

                if ($file['size'] > 2 * 1024 * 1024) {
                    throw new \RuntimeException('Logo muito grande. Máximo 2 MB.');
                }

                $dir = PUBLIC_PATH . '/uploads/logo/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Remove logo antiga
                $antiga = $this->db->fetchOne("SELECT logo FROM config_empresa WHERE id = 1")['logo'] ?? null;
                if ($antiga && file_exists($dir . $antiga)) {
                    unlink($dir . $antiga);
                }

                $logo     = 'logo_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $dir . $logo);
            }

            $campos = [
                'razao_social'  => trim($request->input('razao_social', '')),
                'nome_fantasia' => trim($request->input('nome_fantasia', '')),
                'cnpj'          => trim($request->input('cnpj', '')),
                'telefone'      => trim($request->input('telefone', '')),
                'celular'       => trim($request->input('celular', '')),
                'email'         => trim($request->input('email', '')),
                'site'          => trim($request->input('site', '')),
                'cep'           => trim($request->input('cep', '')),
                'logradouro'    => trim($request->input('logradouro', '')),
                'numero'        => trim($request->input('numero', '')),
                'complemento'   => trim($request->input('complemento', '')),
                'bairro'        => trim($request->input('bairro', '')),
                'cidade'        => trim($request->input('cidade', '')),
                'uf'            => strtoupper(trim($request->input('uf', ''))),
                'cor_primaria'  => $request->input('cor_primaria', '#1A1A1A'),
            ];

            if ($logo) {
                $campos['logo'] = $logo;
            }

            $sets   = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($campos)));
            $campos['id'] = 1;

            $this->db->execute(
                "UPDATE config_empresa SET {$sets} WHERE id = :id",
                $campos
            );

            Session::flash('success', 'Dados da empresa salvos com sucesso.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/config/empresa');
    }
}
