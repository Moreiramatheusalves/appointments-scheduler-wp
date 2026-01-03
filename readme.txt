=== WP Appointments Scheduler ===
Contributors: seu-usuario-wporg
Donate link: https://breniacsoftec.com/
Tags: appointments, booking, schedule, agenda, profissionais, serviços, reservas
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema completo de agendamentos com profissionais, serviços e agenda configurável — desenvolvido por BR Eniac SofTec.

== Description ==

**WP Appointments Scheduler** é um sistema de agendamentos simples, moderno e extensível para WordPress, desenvolvido por **BR Eniac SofTec**.

Ideal para clínicas, barbearias, salões, consultórios, prestadores de serviço e qualquer negócio que precise organizar horários com profissionais.

Com o plugin você pode:

* Cadastrar **profissionais** (nome, contato, status ativo/inativo).
* Cadastrar **categorias de serviço** (organização e filtragem).
* Cadastrar **serviços** com **preço**, **duração**, **descrição** e vínculo com categorias.
* Configurar a **agenda de trabalho** (dias, horários, bloqueios) por profissional.
* Receber **agendamentos no site** através de um **wizard** simples em 3 etapas.
* Gerenciar todos os **agendamentos no painel administrativo**.

### Fluxo do cliente (frontend)

1. O cliente informa os dados básicos (nome, e-mail e/ou telefone).
2. Escolhe profissional, serviço e visualiza os horários disponíveis.
3. Confirma o agendamento e recebe uma mensagem de sucesso.

### Arquitetura

O plugin foi desenvolvido com foco em organização e manutenção:

* **Models** para as entidades (Professional, Service, Category, Agenda, Appointment).
* Camada **Admin** separada (telas do painel WordPress).
* Camada **Public** separada (shortcode, AJAX, assets).
* **Helpers** e **Validator** para sanitização e validação.
* Tabelas próprias no banco usando `dbDelta`.

### Shortcode

Para exibir o wizard em uma página do seu site, use:

`[wpas_booking]`

== Installation ==

1. Faça upload da pasta do plugin para `/wp-content/plugins/wp-appointments-scheduler/`
   ou instale pelo painel do WordPress.
2. Ative o plugin no menu **Plugins**.
3. No painel administrativo, acesse o menu **Agendamentos** e configure:
   * Profissionais
   * Categorias
   * Serviços
   * Agenda
   * Configurações (e-mail de notificação, regras, etc.)
4. Crie uma página (ex.: “Agende seu horário”) e adicione o shortcode:

`[wpas_booking]`

== Frequently Asked Questions ==

= Como exibo o formulário de agendamento no site? =

Crie uma página e adicione o shortcode:

`[wpas_booking]`

Ele renderiza o wizard de agendamento em 3 passos.

= Posso receber notificações por e-mail quando um cliente agenda? =

Sim. Nas **Configurações do plugin** existe o campo **E-mail para notificações**.
Se não for definido, pode assumir o e-mail padrão do administrador do site (dependendo da configuração).

= O plugin remove os dados ao ser desinstalado? =

Existe uma opção em Configurações:

**Excluir dados ao remover o plugin**

Se marcada, ao excluir o plugin no WordPress, as tabelas e opções do WP Appointments Scheduler serão removidas.
Se desmarcada, os dados serão preservados.

= O plugin funciona com múltiplos profissionais? =

Sim. Você pode cadastrar vários profissionais, configurar agenda individual e receber agendamentos para cada um.

== Support ==

Suporte e contato:
Email: contato@breniacsoftec.com  
Site: https://breniacsoftec.com/

== Screenshots ==

1. Cadastro de profissionais.
2. Cadastro de serviços.
3. Agenda mensal (slots e bloqueios).
4. Wizard de agendamento no frontend.

== Changelog ==

= 1.0.0 =
* Lançamento inicial.
* Cadastro de profissionais, categorias e serviços.
* Agenda por profissional (dias, horários, bloqueios).
* Agendamento no frontend via wizard em 3 passos.
* Gestão de agendamentos no admin.
* Opção para excluir dados no uninstall.

== Upgrade Notice ==

= 1.0.0 =
Versão inicial estável do WP Appointments Scheduler.
