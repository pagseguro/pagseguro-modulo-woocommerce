Módulo de integração PagSeguro para WooCommerce
===============================================
---
Descrição
---------
---
Com o módulo instalado e configurado, você pode pode oferecer o PagSeguro como opção de pagamento em sua loja. O módulo utiliza as seguintes funcionalidades que o PagSeguro oferece na forma de APIs:

 - Integração com a [API de Pagamentos]
 - Integração com a [API de Notificações]


Requisitos
----------
---
 - [WordPress] 3.8+
 - [WooCommerce] 2.2+
 - [PHP] 5.4.27+
 - [SimpleXml]
 - [cURL]


Instalação
----------
---
 - Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
 - Baixe o repositório como arquivo zip ou faça um clone;
 - Na área administrativa de seu WordPress acesse o menu Plugins -> Adicionar Novo -> Enviar/Fazer upload do plugin -> aponte para o caminho do arquivo woocommerce-pagseguro-oficial.zip e selecione Instalar Agora;
 - Após a instalação selecione *Ativar plugin*;


Configuração
------------
---
Para acessar e configurar o módulo acesse, na área administrativa de seu WordPress, o menu WooCommerce -> Configurações -> Portais de Pagamento -> PagSeguro. As opções disponíveis estão descritas abaixo.

 - **ativar/desativar**: ativa/desativa o módulo.
 - **título**: Título a ser exibido na tela de pagamento.
 - **descrição**: Descrição a ser exibida na tela de pagamento.
 - **e-mail**: e-mail cadastrado no PagSeguro.
 - **token**: token gerado no PagSeguro.
 - **envinronment**: Ambiente de produção ou desenvolvimento (sandbox).
 - **checkout**: especifica o modelo de checkout que será utilizado. É possível escolher entre checkout padrão,checkout lightbox e transparente.
 - **url de redirecionamento**: ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado automaticamente para a página de confirmação em sua loja ou então para a URL que você informar neste campo. Para ativar o redirecionamento ao final do pagamento é preciso ativar o serviço de [Pagamentos via API]. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje que seus clientes sejam redirecionados para outro local.
 - **url de notificação**: sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje receber as notificações em outro local.
 - **prefixo dos pedidos**: informe um prefixo para diferenciar a origem de suas vendas caso utilize a mesma conta PagSeguro em múltiplas lojas. O formato deve se manter com o prefixo 'WC-' seguido por treze caracteres alphanuméricos.
 - **charset**: codificação do seu sistema (ISO-8859-1 ou UTF-8).
 - **log**: ativa/desativa a geração de logs.
 - **diretório**: informe o local a partir da raíz de instalação do WordPress onde se deseja criar o arquivo de log. Ex.: /logs/ps.log. Caso não informe nada, o log será gravado dentro da pasta wp-content/PagSeguro.log.

Inputs
---------
---
| Dados do comprador         |Tipo  | Esperado                                                                       |
| ---------------------------|:----:|:------------------------------------------------------------------------------:| 
| First Name / Primeiro Nome | {String}                                                             | Nome           | 
| Last Name  / Sobrenome     | {String}                                                             | Sobrenome      |  
| Company  / Empresa         | {String}                                                             | Empresa        | 
| Email                      | {Pattern - ^([a-zA-Z0-9_])+([@])+([a-zA-Z0-9_])+([.])+([a-zA-Z0-9_])}| email@email.em |
| Phone / Telefone           | {Integer} - {DDD+NUMBER}                                             | 99999999999    | 
| Address 1 / Endereço da rua| {String, Integer}                                                    |Endereço, Numero| 
| Address 2 / Complemento    | {String}                                                          | Bairro / Outros...| 
| City / Cidade              | {String}                                                             |    Cidade      |
| PostCode/ CEP              | {Integer or String}                                            | 99999999 / 99999-999 |

Changelog
---------
---
1.4.4
 - Correção de bugs no checkout transparente no redirecionamento e botões de pagamento de débito e boleto.
 - Correção de bugs na url no checkout lightbox

1.4.0
 - Implementado checkout transparente (boleto, debito online e cartão de crédito)
 
1.3.0
 - Implementado conciliação e cancelamento.

1.2.0
 - Implementado checkout com lightbox.

1.1.1
- Ajustes em geral

1.1.0

 - Ajuste na ativação/desativação do módulo.

1.0.0

 - Versão inicial. Integração com API de checkout e API de notificações.


Licença
-------
---
Copyright 2013 PagSeguro Internet LTDA.

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.


Notas
-----
---
 - O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).
 - Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
 - Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
 - Para que ocorra normalmente a geração de logs, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.


[Dúvidas?]
----------
---
Em caso de dúvidas mande um e-mail para desenvolvedores@pagseguro.com.br


Contribuições
-------------
---
Achou e corrigiu um bug ou tem alguma feature em mente e deseja contribuir?

* Faça um fork.
* Adicione sua feature ou correção de bug.
* Envie um pull request no [GitHub].


  [API de Pagamentos]: https://dev.pagseguro.uol.com.br/documentacao/pagamentos
  [API de Notificações]: https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html
  [Dúvidas?]: https://comunidade.pagseguro.uol.com.br/hc/pt-br/community/topics
  [Pagamentos via API]: https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml
  [Notificação de Transações]: https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml
  [WordPress]: http://wordpress.org/
  [WooCommerce]: http://www.woothemes.com/woocommerce/
  [PHP]: http://www.php.net/
  [SPL]: http://php.net/manual/en/book.spl.php
  [cURL]: http://php.net/manual/en/book.curl.php
  [DOM]: http://php.net/manual/en/book.dom.php
  [GitHub]: https://github.com/pagseguro/woocommerce
  [SimpleXml]: http://php.net/manual/en/book.simplexml.php

