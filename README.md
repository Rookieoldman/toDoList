# La Meva Llista de Tasques (Laravel + Vue 3)

Aquest és un projecte simple de llista de tasques desenvolupat amb Laravel 10 per al backend (API) i Vue 3 (Composition API) amb Vite per al frontend.

## Requisits Prevists

*   PHP >= 8.1 (o la versió requerida pel teu Laravel)
*   Composer
*   Node.js i npm
*   Un servidor de base de dades (ex. MySQL, MariaDB, PostgreSQL)

## Instal·lació

1.  **Clonar el repositori:**
    ```bash
    git clone https://github.com/Rookieoldman/toDoList.git
    cd toDoList
    ```

2.  **Instal·lar dependències de Backend (PHP/Laravel):**
    ```bash
    composer install
    ```

3.  **Configuració de l'Entorn:**
    *   Copia el fitxer d'exemple `.env.example` a `.env`:
        ```bash
        cp .env.example .env
        ```
    *   Edita el fitxer `.env` amb la configuració de la teva base de dades (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Assegura't d'haver creat la base de dades especificada.
    *   Genera la clau de l'aplicació:
        ```bash
        php artisan key:generate
        ```

4.  **Executar les Migracions:** Això crearà la taula `tasks` a la teva base de dades.
    ```bash
    php artisan migrate
    ```

5.  **Instal·lar dependències de Frontend (Node.js/Vue):**
    *   Aquesta comanda instal·larà totes les dependències llistades a `package.json`, incloent Vue, Vite, Axios, etc.
        ```bash
        npm install
        ```
    *   **Dependències Clau del Frontend:**
        El projecte utilitza les següents llibreries principals per al frontend, les quals han de ser instal·lades per la comanda `npm install` anterior si el fitxer `package.json` està actualitzat:
        *   **Vue 3 (`vue`):** El framework progressiu de JavaScript. (Instal·lat amb `npm install vue@latest --save-dev`).
        *   **Vite (`vite`):** Eina de construcció i servidor de desenvolupament ràpid per al frontend. (Normalment inclòs per Laravel).
        *   **Plugin Vue per a Vite (`@vitejs/plugin-vue`):** Necessari perquè Vite pugui processar i compilar fitxers `.vue`. (Instal·lat amb `npm install @vitejs/plugin-vue --save-dev`).
        *   **Axios (`axios`):** Client HTTP basat en promeses per fer les crides a l'API de Laravel. (Instal·lat amb `npm install axios`).
        *   **Laravel Vite Plugin (`laravel-vite-plugin`):** Pont entre Laravel i Vite. (Normalment inclòs per Laravel).

## Execució en Desenvolupament

Necessitaràs **dues terminals** obertes al directori arrel del projecte (`toDoList`):

1.  **Terminal 1: Servidor de Vite (Compila i serveix el frontend):**
    ```bash
    npm run dev
    ```
    Vite observarà els canvis als teus fitxers Vue/JS/CSS i els compilarà sobre la marxa.

2.  **Terminal 2: Servidor de Laravel (Serveix el backend i la pàgina inicial):**
    ```bash
    php artisan serve
    ```

3.  **Accedir a l'aplicació:** Obre el teu navegador web i visita l'URL proporcionada per `php artisan serve` (generalment `http://127.0.0.1:8000`).

## Funcionalitats Implementades

*   Veure la llista de tasques.
*   Afegir noves tasques.
*   Marcar tasques com a completades/incompletes (fent clic al botó "Done"/"Undo").
*   Editar el títol de les tasques (fent clic a "Edit").
*   Esborrar tasques (fent clic a "×").

---