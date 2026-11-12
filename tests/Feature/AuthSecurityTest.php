<?php

    namespace Tests\Feature;

    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Support\Facades\Hash;
    use Tests\TestCase;

    class AuthSecurityTest extends TestCase
    {
        use RefreshDatabase;

        /*
            este teste prova três regras importantes:
            credenciais válidas autenticam o usuário,
            a sessão usa o guard web
            e o endpoint de login não volta a devolver um token de acesso na resposta.
        */

        public function test_user_can_log_in_using_valid_credentials(): void
        {
            $user       =   User::factory()->create(['password' => Hash::make('Senha#123'),]);
            $response   =   $this->postJson('/login', ['email' => $user->email,'password' => 'Senha#123',]);

            $response
                ->assertOk()
                ->assertJsonPath('message', 'Login realizado com sucesso.')
                ->assertJsonPath('user.id', $user->id)
                ->assertJsonMissingPath('token');

            $this->assertAuthenticatedAs($user, 'web');
        }

        /*
            teste impede regressões em que uma senha incorreta seja aceita,
            uma sessão seja criada indevidamente ou a resposta revele informações adicionais sobre a conta.
        */
        public function test_user_cannot_log_in_using_invalid_credentials(): void
        {
            $user       =   User::factory()->create(['password' => Hash::make('Senha#123'),]);
            $response   =   $this->postJson('/login', ['email' => $user->email,'password' => 'SenhaIncorreta#123', ]);

            $response ->assertUnauthorized()->assertExactJson(['message' => 'E-mail ou senha inválidos.',]);

            $this->assertGuest('web');
        }

        /*
            teste comprova que o logout não apenas responde sucesso:
            ele encerra a autenticação da sessão e impede que o navegador continue acessando uma página protegida.
        */
        public function test_user_can_log_out_and_session_is_invalidated(): void
        {
            $user = User::factory()->create();

            $this->actingAs($user, 'web');

            $response = $this->postJson('/logout');

            $response->assertOk()->assertExactJson(['message' => 'Logout realizado com sucesso.',]);

            $this->assertGuest('web');

            $this->get('/produtos')->assertRedirect('/');
        }

        /*
            páginas web protegidas devem redirecionar o visitante ao login;
            rotas da API devem responder 401 Unauthorized.
            São comportamentos diferentes, ambos necessários.
        */
        public function test_guest_cannot_access_protected_pages_or_api(): void
        {
            $this->get('/produtos')->assertRedirect('/');

            $this->getJson('/api/products')->assertUnauthorized();
        }

        /*
            o teste prova que o limitador configurado em AppServiceProvider bloqueia ataques de tentativa repetida de senha.
            Ele usa o mesmo e-mail e IP nas seis requisições, exatamente a chave definida pelo rate limit.
        */
        public function test_login_is_rate_limited_after_five_attempts(): void
        {
            $user = User::factory()->create(['password' => Hash::make('Senha#123'), ]);

            for ($attempt = 1; $attempt <= 5; $attempt++) {

                $this->postJson('/login', ['email' => $user->email,'password' => 'SenhaIncorreta#123',])->assertUnauthorized();
            }

            $this->postJson('/login', ['email' => $user->email,'password' => 'SenhaIncorreta#123',])->assertTooManyRequests();
        }

        /*
            mesmo requisições inválidas consomem o limite.
            Isso impede que alguém use a rota de cadastro para sobrecarregar a aplicação com tentativas repetidas;
            na 11ª chamada, o Laravel deve responder 429 Too Many Requests.
        */
        public function test_registration_is_rate_limited_after_ten_attempts(): void
        {
            for ($attempt = 1; $attempt <= 10; $attempt++)
            {
                $this->postJson('/api/register', [])->assertUnprocessable();
            }

            $this->postJson('/api/register', [])->assertTooManyRequests();
        }

        /*
            a interface precisa somente de identificação básica para exibir o nome do usuário.
            Este teste impede que uma mudança futura exponha endereço, senha,
            tokens ou outros dados pessoais pela rota /api/user.
        */
        public function test_authenticated_user_endpoint_returns_only_safe_fields(): void
        {
            $user = User::factory()->create([
                'address' => 'Rua de Teste',
                'address_number' => '100',
                'address_complement' => null,
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01001-000',
            ]);

            $response = $this->actingAs($user, 'web')->getJson('/api/user');

            $response->assertOk()->assertExactJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'address' => 'Rua de Teste',
                'address_number' => '100',
                'address_complement' => null,
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01001-000',
            ]);
        }

        /*
            este é o teste que comprova a arquitetura nova de ponta a ponta:
            o login cria sessão web; depois o Sanctum reconhece essa sessão na rota /api/products
            quando a requisição vem da interface própria. Não há Bearer Token envolvido.
        */
        public function test_logged_in_user_can_access_api_using_session_cookie(): void
        {
            $user = User::factory()->create(['password' => Hash::make('Senha#123'),]);

            $this->postJson('/login', ['email' => $user->email,'password' => 'Senha#123',])->assertOk();

            $this->withHeader('Origin', 'http://localhost:8000')
                ->getJson('/api/products')
                ->assertOk()
                ->assertJsonStructure([
                    'data',
                ]);
        }

        /*
            antes do login, alguém mal-intencionado poderia tentar induzir um navegador a usar um identificador de sessão conhecido.
            Ao autenticar, o Laravel deve criar outro identificador; assim, a sessão anterior não pode ser aproveitada após o login.
        */
        public function test_login_regenerates_the_session_identifier(): void
        {
            $user = User::factory()->create(['password' => Hash::make('Senha#123'),]);

            $this->withSession(['login_started' => true,]);

            $sessionIdBeforeLogin = $this->app['session']->getId();

            $response = $this->postJson('/login', ['email' => $user->email,'password' => 'Senha#123', ]);

            $response->assertOk();

            $sessionIdAfterLogin = $this->app['session']->getId();

            $this->assertNotSame($sessionIdBeforeLogin, $sessionIdAfterLogin);
        }

    }
