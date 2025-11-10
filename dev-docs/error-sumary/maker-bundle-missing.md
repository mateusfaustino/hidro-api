# Erro ao carregar Symfony MakerBundle

## Descrição do problema
Ao acessar a rota `/api/v1/hello_world` o framework tentava inicializar o `Symfony\\Bundle\\MakerBundle\\MakerBundle`, porém a dependência não estava instalada no ambiente atual (instalação sem pacotes de desenvolvimento). Isso fazia com que o autoloader não encontrasse a classe `MakerBundle`, resultando no erro:

```
Attempted to load class "MakerBundle" from namespace "Symfony\\Bundle\\MakerBundle".
Did you forget a "use" statement for another namespace?
```

## Causa raiz
O arquivo `config/bundles.php` registrava o MakerBundle incondicionalmente. Em ambientes onde os pacotes de desenvolvimento não estão instalados (`composer install --no-dev`), a classe não fica disponível, causando o erro na fase de bootstrap da aplicação.

## Solução aplicada
Atualizamos `config/bundles.php` para registrar o MakerBundle apenas quando a classe existir. Dessa forma, o bundle só é carregado em instalações onde a dependência está presente (por exemplo, ambientes de desenvolvimento).

```php
if (class_exists(Symfony\\Bundle\\MakerBundle\\MakerBundle::class)) {
    $bundles[Symfony\\Bundle\\MakerBundle\\MakerBundle::class] = ['dev' => true];
}
```

Após essa alteração, a aplicação inicializa corretamente em ambientes sem dependências de desenvolvimento, e a rota `/api/v1/hello_world` volta a responder normalmente.
