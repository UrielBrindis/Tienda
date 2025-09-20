<header>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <img src="images/Designer.png" alt="Logo" width="50" height="50" class="me-2">
        <strong>Twenty One Records</strong>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
              data-bs-target="#navbarHeader" aria-controls="navbarHeader" 
              aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarHeader">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a href="index.php" class="nav-link active">Catálogo</a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link active">Contacto</a>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <a href="checkout.php" class="btn btn-primary btn-sm">
            <i class="fas fa-shopping-cart"></i> Carrito 
            <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart ?? 0; ?></span>
          </a>
          
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="historial.php" class="btn btn-secondary btn-sm">
              <i class="fas fa-history"></i> Historial
            </a>
          <?php endif; ?>

          <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
              <button class="btn btn-success btn-sm dropdown-toggle" type="button" 
                      id="btnSession" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnSession">
                <li>
                  <a class="dropdown-item" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                  </a>
                </li>
              </ul>
            </div>
          <?php else: ?>
            <a href="login.php" class="btn btn-success btn-sm">
              <i class="fas fa-user"></i> Ingresar
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
</header>
