<?php
require_once 'assets/dbc.php';
require_once 'formularios/cat/seg/menu_builder.php'; ?>

<div class="accordion" id="menuAccordion">

<?php foreach ($menu as $moduloId => $modulo): ?>
  <div class="accordion-item bg-dark border-0">
    <h2 class="accordion-header" id="heading<?= $moduloId ?>">
      <button class="accordion-button collapsed text-white"
              style="background-color:#4682B4"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapse<?= $moduloId ?>">
        <?= htmlspecialchars($modulo['nombre']) ?>
      </button>
    </h2>

    <div id="collapse<?= $moduloId ?>"
         class="accordion-collapse collapse"
         data-bs-parent="#menuAccordion">

      <div class="accordion-body p-0">
        <ul class="nav flex-column">

        <?php foreach ($modulo['opciones'] as $op): ?>
          <li class="nav-item">
            <a class="nav-link bg-light text-dark menu-link"
               href="<?= htmlspecialchars($op['ruta']) ?>">
              <i class="bi bi-dash"></i>
              <?= htmlspecialchars($op['nombre']) ?>
            </a>
          </li>
        <?php endforeach; ?>

        </ul>
      </div>
    </div>
  </div>
<?php endforeach; ?>

</div>
