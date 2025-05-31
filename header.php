<?php
session_start();
if (isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    if (is_array($_SESSION['user'])) {
        if (isset($_SESSION['user']['id'])) {
            $_SESSION['user_id'] = $_SESSION['user']['id'];
        } elseif (isset($_SESSION['user']['user_id'])) {
            $_SESSION['user_id'] = $_SESSION['user']['user_id'];
        }
    }
}
?>
<html lang="fr">
<!-- باقي محتوى الصفحة -->
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homeverse - Trouvez la maison de vos rêves</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- 
    - favicon
  -->
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
  
  <!-- bn
    - lien CSS personnalisé
  -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">

  <!-- 
    - lien Google Fonts
  -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap"
    rel="stylesheet">
</head>
<body>
    <!-- 
    - #EN-TÊTE
  -->

<header class="header" data-header>

    <div class="overlay" data-overlay></div>

    <div class="header-top">
      <div class="container">

        <ul class="header-top-list">

          <li>
          <a href="#" onclick="alert('sanagha707@gmail.com'); return false;" class="header-top-link">
              <ion-icon name="mail-outline"></ion-icon>

              <span>sanagha707@gmail.com</span>
            </a>
          </li>

          <li>
            <a href="#" class="header-top-link">
              <ion-icon name="location-outline"></ion-icon>

              <address>Annaba,Sidi Ammar</address>
            </a>
          </li>

        </ul>

        <div class="wrapper">
          <ul class="header-top-social-list">

            <li>
              <a href="#" class="header-top-social-link">
                <ion-icon name="logo-facebook"></ion-icon>
              </a>
            </li>

            <li>
              <a href="#" class="header-top-social-link">
                <ion-icon name="logo-twitter"></ion-icon>
              </a>
            </li>

            <li>
              <a href="#" class="header-top-social-link">
                <ion-icon name="logo-instagram"></ion-icon>
              </a>
            </li>

            <li>
              <a href="#" class="header-top-social-link">
                <ion-icon name="logo-pinterest"></ion-icon>
              </a>
            </li>

          </ul>

         <button class="header-top-btn" onclick="window.location.href='http://localhost/immobilier/bub.php'">
    Ajouter une annonce
</button>
        </div>

      </div>
    </div>

    <div class="header-bottom">
      <div class="container">

        <a href="" class="logo">
         ImmoDZ
        </a>

        <nav class="navbar" data-navbar>

          <div class="navbar-top">

            <a href="#" class="logo">
              <img src="photo.png" alt="Logo Homeverse">
            </a>

            <button class="nav-close-btn" data-nav-close-btn aria-label="Fermer le menu">
              <ion-icon name="close-outline"></ion-icon>
            </button>

          </div>

       <div class="navbar-bottom">
    <ul class="navbar-list">
        <li>
            <a href="Untitled-1.php#home" class="navbar-link" data-nav-link>Accueil</a>
        </li>

        <li>
            <a href="Untitled-1.php#about" class="navbar-link" data-nav-link>À propos</a>
        </li>

        <li>
            <a href="Untitled-1.php#service" class="navbar-link" data-nav-link>Services</a>
        </li>

        <li>
            <a href="Untitled-1.php#property" class="navbar-link" data-nav-link>Biens</a>
        </li>

        <li>
            <a href="Untitled-1.php#contact" class="navbar-link" data-nav-link>Contact</a>
        </li>
    </ul>
</div>

        </nav>

        <div class="header-bottom-actions">
          <button id="profile-btn" class="header-bottom-actions-btn avatar-mode" aria-label="Profil">
            <?php if (isset($_SESSION['user'])): ?>
              <a href="compte.php" class="profile-avatar-link" style="display: flex; align-items: center; gap: 5px; color: inherit; text-decoration: none;">
                <?php if (!empty($_SESSION['user']['avatar'])): ?>
                  <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar']); ?>" alt="صورة المستخدم" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                  <div class="profile-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: #3498db; color: white; display: flex; justify-content: center; align-items: center;">
                    <?php 
                    $name = $_SESSION['user']['name'] ?? '';
                    $initial = !empty($name) ? strtoupper(substr($name, 0, 1)) : '';
                    echo $initial;
                    ?>
                  </div>
                <?php endif; ?>
                <span><?php echo htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0] ?? 'Profil'); ?></span>
              </a>
            <?php else: ?>
              <ion-icon name="person-outline"></ion-icon>
              <span>Profil</span>
            <?php endif; ?>
          </button>


    
         

          <button class="header-bottom-actions-btn" data-nav-open-btn aria-label="Ouvrir le menu">
            <ion-icon name="menu-outline"></ion-icon>
            <span>Menu</span>
          </button>
 

        </div>
               

      </div>
    </div>

    <!-- نافذة تسجيل الدخول/التسجيل -->
<div class="auth-modal" id="authModal">
  <div class="auth-container" id="authSignup">
    <h1 class="auth-title">S'inscrire</h1>
    <form class="auth-form" method="post" action="register.php" id="registerForm">
      <input type="hidden" name="auth_type" value="signup">
      
      <div class="auth-input-group">
        <input type="text" id="authFname" name="fname" placeholder=" " required>
        <label for="authFname">Prénom</label>
        <span class="error-message" id="fnameError"></span>
      </div>
      
      <div class="auth-input-group">
        <input type="text" id="authLname" name="lname" placeholder=" " required>
        <label for="authLname">Nom</label>
        <span class="error-message" id="lnameError"></span>
      </div>
      
      <div class="auth-input-group">
        <input type="email" id="authEmail" name="email" placeholder=" " required>
        <label for="authEmail">Email</label>
        <span class="error-message" id="emailError"></span>
      </div>
      
      <div class="auth-input-group">
        <input type="password" id="authPassword" name="password" placeholder=" " required minlength="8">
        <label for="authPassword">Mot de passe</label>
        <span class="error-message" id="passwordError"></span>

      </div>
      <!-- حقل رقم الهاتف الجديد -->
      <div class="auth-input-group">
        <input type="tel" id="authPhone" name="phone_number" placeholder=" " required>
        <label for="authPhone">Numéro de téléphone</label>
        <span class="error-message" id="phoneError"></span>
      </div>
      
      <button type="submit" class="auth-submit-btn">S'inscrire</button>
    </form>

    <div class="auth-separator">
      <span>ou</span>
    </div>
    
    <div class="auth-social-btns">
      <button type="button" class="auth-social-btn google">
        <i class="fab fa-google"></i>
      </button>
      <button type="button" class="auth-social-btn facebook">
        <i class="fab fa-facebook-f"></i>
      </button>
    </div>
    
    <div class="auth-switch">
      Vous avez déjà un compte? 
      <button type="button" class="auth-switch-btn" data-target="authSignin">Se connecter</button>
    </div>
  </div>

  <div class="auth-container" id="authSignin">
    <h1 class="auth-title">Se connecter</h1>
    <form class="auth-form" method="post" action="login.php" id="loginForm">
      <input type="hidden" name="auth_type" value="login">
      
      <div class="auth-input-group">
         <input type="email" id="authLoginEmail" name="email" placeholder=" " required>
         <label for="authLoginEmail">Email</label>
          <span class="error-message" id="loginEmailError"></span>
      </div>
      
      <div class="auth-input-group">
        <input type="password" id="authLoginPassword" name="password" placeholder=" " required>
        <label for="authLoginPassword">Mot de passe</label>
        <span class="error-message" id="loginPasswordError"></span>
      </div>
      
      <button type="submit" class="auth-submit-btn">Se connecter</button>
    </form>

    <div class="auth-separator">
      <span>ou</span>
    </div>
    
    <div class="auth-social-btns">
      <button type="button" class="auth-social-btn google">
        <i class="fab fa-google"></i>
      </button>
      <button type="button" class="auth-social-btn facebook">
        <i class="fab fa-facebook-f"></i>
      </button>
    </div>
    
    <div class="auth-switch">
      Pas encore de compte? 
      <button type="button" class="auth-switch-btn" data-target="authSignup">S'inscrire</button>
    </div>
  </div>
  
  <button type="button" class="auth-close-btn">&times;</button>
</div>
<div class="auth-overlay" id="authOverlay"></div>



<?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
    <li><a href="account.php?section=admin-panel"><i class="fas fa-shield-alt"></i> لوحة التحكم</a></li>
<?php endif; ?>
</header>

<script>
  'use strict';
  
  /**
   * element toggle function
   */
  
  const elemToggleFunc = function (elem) { elem.classList.toggle("active"); }
  
  
  
  /**
   * navbar toggle
   */
  
  const navbar = document.querySelector("[data-navbar]");
  const overlay = document.querySelector("[data-overlay]");
  const navCloseBtn = document.querySelector("[data-nav-close-btn]");
  const navOpenBtn = document.querySelector("[data-nav-open-btn]");
  const navbarLinks = document.querySelectorAll("[data-nav-link]");
  
  const navElemArr = [overlay, navCloseBtn, navOpenBtn];
  
  /**
   * close navbar when click on any navbar link
   */
  
  for (let i = 0; i < navbarLinks.length; i++) { navElemArr.push(navbarLinks[i]); }
  
  /**
   * addd event on all elements for toggling navbar
   */
  
  for (let i = 0; i < navElemArr.length; i++) {
    navElemArr[i].addEventListener("click", function () {
      elemToggleFunc(navbar);
      elemToggleFunc(overlay);
    });
  }
  
  
  
  /**
   * header active state
   */
  
  const header = document.querySelector("[data-header]");
  
  window.addEventListener("scroll", function () {
    window.scrollY >= 400 ? header.classList.add("active")
      : header.classList.remove("active");
  }); 
  


  const profileBtn = document.getElementById('profile-btn');
    const authModal = document.getElementById('authModal');
    const authOverlay = document.getElementById('authOverlay');
    const authCloseBtn = document.querySelector('.auth-close-btn');
    const authSwitchBtns = document.querySelectorAll('.auth-switch-btn');
    const authSignin = document.getElementById('authSignin');
    const authSignup = document.getElementById('authSignup');

    // فتح نافذة المصادقة
    profileBtn.addEventListener('click', function() {
      authModal.style.display = 'block';
      authOverlay.style.display = 'block';
      authSignin.classList.add('active');
      authSignup.classList.remove('active');
    });

    // إغلاق نافذة المصادقة
    authCloseBtn.addEventListener('click', closeAuthModal);
    authOverlay.addEventListener('click', closeAuthModal);

    function closeAuthModal() {
      authModal.style.display = 'none';
      authOverlay.style.display = 'none';
    }

    // التبديل بين تسجيل الدخول والتسجيل
    authSwitchBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const target = this.getAttribute('data-target');
        authSignin.classList.remove('active');
        authSignup.classList.remove('active');
        document.getElementById(target).classList.add('active');
      });
    });

    // إغلاق عند الضغط على زر Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && authModal.style.display === 'block') {
        closeAuthModal();
      }
    });

































 document.addEventListener('DOMContentLoaded', function() {
  // عناصر DOM
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const profileBtn = document.getElementById('profile-btn');
  const closeBtn = document.querySelector('.auth-close-btn');
  const switchButtons = document.querySelectorAll('.auth-switch-btn');
  const authModal = document.getElementById('authModal');
  const authOverlay = document.getElementById('authOverlay');

  // تحقق من جلسة المستخدم عند التحميل
  checkUserSession();

  // أحداث تسجيل الدخول
  if (loginForm) {
    loginForm.addEventListener('submit', handleLogin);
  }

  // أحداث التسجيل
  if (registerForm) {
    registerForm.addEventListener('submit', handleRegister);
  }

  // أحداث الواجهة
  if (closeBtn) {
    closeBtn.addEventListener('click', closeAuthModal);
  }

  if (switchButtons.length > 0) {
    switchButtons.forEach(button => {
      button.addEventListener('click', switchAuthForm);
    });
  }

  // ===== دوال المعالجة =====

  async function handleLogin(e) {
    e.preventDefault();
    clearErrors('loginEmailError', 'loginPasswordError');

    const formData = new FormData(loginForm);
    const loginData = {
      email: formData.get('email'),
      password: formData.get('password')
    };

    try {
      const response = await fetch('login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(loginData)
      });

      if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
      
      const data = await response.json();
      
      if (data.success) {
        sessionStorage.setItem('currentUser', JSON.stringify(data.user));
        updateProfileUI(data.user);
        closeAuthModal();
        // window.location.href = 'profile.php'; // إلغاء التعليق إذا كنت تريد إعادة التوجيه
      } else {
        displayErrors(data.errors);
        if (data.message) alert(data.message);
      }
    } catch (error) {
      console.error('Login error:', error);
      alert('حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى.');
    }
  }

  async function handleRegister(e) {
    e.preventDefault();
    clearErrors('fnameError', 'lnameError', 'phoneError', 'emailError', 'passwordError');

    const formData = new FormData(registerForm);
    const registerData = {
      fname: formData.get('fname'),
      lname: formData.get('lname'),
      phone_number: formData.get('phone_number'),
      email: formData.get('email'),
      password: formData.get('password')
    };

    try {
      const response = await fetch('register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(registerData)
      });

      if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
      
      const data = await response.json();
      
      if (data.success) {
        sessionStorage.setItem('currentUser', JSON.stringify(data.user));
        updateProfileUI(data.user);
        closeAuthModal();
        // window.location.href = 'profile.php'; // إلغاء التعليق إذا كنت تريد إعادة التوجيه
      } else {
        displayErrors(data.errors);
        if (data.message) alert(data.message);
      }
    } catch (error) {
      console.error('Registration error:', error);
      alert('حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.');
    }
  }

  function switchAuthForm() {
    const target = this.getAttribute('data-target');
    document.querySelectorAll('.auth-container').forEach(container => {
      container.style.display = 'none';
    });
    document.getElementById(target).style.display = 'block';
  }

  // ===== دوال المساعدة =====

  function checkUserSession() {
    const storedUser = sessionStorage.getItem('currentUser');
    if (storedUser) {
      try {
        const user = JSON.parse(storedUser);
        updateProfileUI(user);
      } catch (e) {
        console.error('Error parsing user data:', e);
        sessionStorage.removeItem('currentUser');
      }
    }
  }

  function updateProfileUI(user) {
    if (!user || !profileBtn) return;
    
    const fullName = user.name || `${user.fname || ''} ${user.lname || ''}`.trim();
    const firstName = fullName.split(' ')[0] || 'U';
    const avatarChar = firstName.charAt(0).toUpperCase();
     
    profileBtn.innerHTML = `
      <a href="compte.php" class="profile-avatar-link" style="display: flex; align-items: center; gap: 5px; color: inherit; text-decoration: none;">
        <div class="profile-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: #3498db; color: white; display: flex; justify-content: center; align-items: center;">
          ${avatarChar}
        </div>
        ${fullName ? `<span>${firstName}</span>` : ''}
      </a>
    `;
   

  }

  function closeAuthModal() {
    if (authModal) authModal.style.display = 'none';
    if (authOverlay) authOverlay.style.display = 'none';
  }

  function clearErrors(...errorIds) {
    errorIds.forEach(id => {
      const element = document.getElementById(id);
      if (element) element.textContent = '';
    });
  }

  function displayErrors(errors) {
    if (!errors) return;
    Object.entries(errors).forEach(([field, message]) => {
      const errorElement = document.getElementById(`${field}Error`);
      if (errorElement) errorElement.textContent = message;
    });
  }






   function updateProfileAvatar(avatarUrl) {
        const profileBtn = document.getElementById('profile-btn');
        if (profileBtn) {
          const avatarContainer = profileBtn.querySelector('.profile-avatar-link');
          if (avatarContainer) {
            if (avatarUrl) {
              avatarContainer.innerHTML = `
                <img src="${avatarUrl}" alt="صورة المستخدم" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                <span>${avatarContainer.querySelector('span')?.textContent || 'Profil'}</span>
              `;
            } else {
              const name = '<?php echo $_SESSION['user']['name'] ?? ''; ?>';
              const initial = name ? name.charAt(0).toUpperCase() : 'P';
              avatarContainer.innerHTML = `
                <div class="profile-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: #3498db; color: white; display: flex; justify-content: center; align-items: center;">
                  ${initial}
                </div>
                <span>${name ? name.split(' ')[0] : 'Profil'}</span>
              `;
            }
          }
        }
      }

      // يمكن استدعاء هذه الدالة عند تغيير الصورة في صفحة الحساب
      // مثال: updateProfileAvatar('مسار/الصورة/الجديدة.jpg');
    });






  </script>
  <style>
         :root {
  
  /**
   * colors
   */
  
  --dark-jungle-green: hsl(188, 63%, 7%);
  --prussian-blue: hsl(200, 69%, 14%);
  --raisin-black-1: hsl(227, 29%, 13%);
  --raisin-black-2: hsl(229, 17%, 19%);
  --yellow-green: hsl(89, 72%, 45%);
  --orange-soda: hsl(9, 100%, 62%);
  --cultured-1: hsl(0, 0%, 93%);
  --cultured-2: hsl(192, 24%, 96%);
  --misty-rose: hsl(7, 56%, 91%);
  --alice-blue: hsl(210, 100%, 97%);
  --seashell: hsl(8, 100%, 97%);
  --cadet: hsl(200, 15%, 43%);
  --white: hsl(0, 0%, 100%);
  --black: hsl(0, 0%, 0%);
  --opal: hsl(180, 20%, 62%);
  
  /**
   * typography
   */
  
  --ff-nunito-sans: "Nunito Sans", sans-serif;
  --ff-poppins: "Poppins", sans-serif;
  
  --fs-1: 1.875rem;
  --fs-2: 1.5rem;
  --fs-3: 1.375rem;
  --fs-4: 1.125rem;
  --fs-5: 0.875rem;
  --fs-6: 0.813rem;
  --fs-7: 0.75rem;
  
  --fw-500: 500;
  --fw-600: 600;
  --fw-700: 700;
  
  /**
   * transition
   */
  
  --transition: 0.25s ease;
  
  /**
   * spacing
   */
  
  --section-padding: 100px;
  
  /**
   * shadow
   */
  
  --shadow-1: 0 5px 20px 0 hsla(219, 56%, 21%, 0.1);
  --shadow-2: 0 16px 32px hsla(188, 63%, 7%, 0.1);
  
  }
  
  
  
  
  /*-----------------------------------*\
  #RESET
  \*-----------------------------------*/
  
  *, *::before, *::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  }
  
  li { list-style: none; }
  
  a { text-decoration: none; }
  
  a,
  img,
  span,
  button,
  ion-icon { display: block; }
  
  button {
  border: none;
  background: none;
  font: inherit;
  text-align: left;
  cursor: pointer;
  }
  
  address { font-style: normal; }
  
  ion-icon { pointer-events: none; }
  
  html {
  font-family: var(--ff-nunito-sans);
  scroll-behavior: smooth;
  }
  
  body {
  background: var(--white);
  overflow-x: hidden;
  }
  
  ::-webkit-scrollbar {
  width: 10px;
  height: 8px;
  }
  
  ::-webkit-scrollbar-track { background: var(--white); }
  
  ::-webkit-scrollbar-thumb {
  background: var(--cadet);
  border-left: 2px solid var(--white);
  }
  
  
  
  
  
  /*-----------------------------------*\
  #REUSED STYLE
  \*-----------------------------------*/
  
  .container { padding-inline: 15px; }
  
  button, a { transition: var(--transition); }
  
  .h1,
  .h2,
  .h3 {
  color: var(--dark-jungle-green);
  font-family: var(--ff-poppins);
  line-height: 1.3;
  }
  
  .h1 {
  font-size: var(--fs-1);
  line-height: 1;
  }
  
  .h2 { font-size: var(--fs-2); }
  
  .h3 {
  font-size: var(--fs-4);
  font-weight: var(--font-weight, 700);
  }
  
  .h3 > a { color: inherit; }
  
  .btn {
  position: relative;
  background: var(--orange-soda);
  color: var(--white);
  font-family: var(--ff-poppins);
  font-size: var(--fs-5);
  text-transform: var(--text-transform, capitalize);
  border: 1px solid var(--orange-soda);
  padding: 10px 20px;
  z-index: 1;
  }
  
  .btn:is(:hover, :focus) {
  background: var(--black);
  color: var(--dark-jungle-green);
  border-color: var(--black);
  }
  
  .btn::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 100%;
  background: var(--white);
  transition: var(--transition);
  z-index: -1;
  }
  
  .btn:is(:hover, :focus)::before { width: 100%; }
  
  .w-100 { width: 100%; }
  
  .section-subtitle {
  color: var(--orange-soda);
  font-size: var(--fs-5);
  font-weight: var(--fw-600);
  padding: 5px 20px;
  background: hsla(9, 100%, 62%, 0.1);
  width: max-content;
  border-radius: 50px;
  margin-inline: auto;
  margin-bottom: 15px;
  }
  
  .section-title {
  text-align: var(--text-align, center);
  margin-bottom: var(--margin-bottom, 50px);
  }
  
  .card-badge {
  background: var(--black);
  color: var(--white);
  font-size: var(--fs-7);
  text-transform: uppercase;
  position: absolute;
  top: 15px;
  right: 15px;
  padding: 4px 10px;
  }
  
  .card-badge.green { background: var(--yellow-green); }
  
  .card-badge.orange { background: var(--orange-soda); }
  
  .has-scrollbar {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  overflow-x: auto;
  margin-inline: -15px;
  padding-inline: 15px;
  scroll-padding-left: 15px;
  padding-bottom: 60px;
  scroll-snap-type: inline mandatory;
  }
  
  .has-scrollbar > li {
  min-width: 100%;
  scroll-snap-align: start;
  }
  
  .has-scrollbar::-webkit-scrollbar-track {
  background: var(--cultured-2);
  outline: 2px solid var(--cadet);
  border-radius: 10px;
  }
  
  .has-scrollbar::-webkit-scrollbar-thumb {
  background: var(--cadet);
  border: 1px solid var(--cultured-2);
  border-radius: 10px;
  }
  
  .has-scrollbar::-webkit-scrollbar-button { width: 15%; }
  
  
  
  
  
  /*-----------------------------------*\
  #HEADER
  \*-----------------------------------*/
  
  .header {
  position: relative;
  z-index: 2;
  }
  
  .header-top {
  background: var(--prussian-blue);
  padding-block: 15px;
  }
  
  .header-top .container,
  .header-top-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  }
  
  .header-top .container { gap: 8px 20px; }
  
  .header-top-list { gap: 15px; }
  
  .header-top-link {
  color: var(--white);
  font-size: var(--fs-6);
  font-weight: var(--fw-700);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 5px;
  }
  
  .header-top-link:is(:hover, :focus) { color: var(--orange-soda); }
  
  .header-top-link ion-icon {
  color: var(--orange-soda);
  font-size: 15px;
  --ionicon-stroke-width: 60px;
  }
  
  .header-top .wrapper,
  .header-top-social-list {
  display: flex;
  align-items: center;
  }
  
  .header-top .wrapper { gap: 20px; }
  
  .header-top-social-list { gap: 8px; }
  
  .header-top-social-link {
  color: var(--white);
  font-size: 15px;
  }
  
  .header-top-btn {
  background: var(--orange-soda);
  color: var(--white);
  font-size: var(--fs-6);
  font-weight: var(--fw-700);
  padding: 4px 15px;
  }
  
  .header-top-btn:is(:hover, :focus) { --orange-soda: hsl(7, 72%, 46%); }
  
  .header-bottom {
  background: var(--white);
  padding-block: 25px;
  }
  
  .header-bottom .logo img { margin-inline: auto; }
  
  .navbar {
  background: var(--white);
  position: fixed;
  top: 0;
  left: -310px;
  max-width: 300px;
  width: 100%;
  height: 100%;
  box-shadow: 0 3px 10px hsla(0, 0%, 0%, 0.3);
  z-index: 5;
  padding: 60px 20px;
  visibility: hidden;
  transition: 0.15s ease-in;
  }
  
  .navbar.active {
  visibility: visible;
  transform: translateX(310px);
  transition: 0.25s ease-out;
  }
  
  .navbar-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 40px;
  border-bottom: 1px solid var(--cultured-1);
  margin-bottom: 25px;
  }
  
  .navbar-top .logo img { width: 200px; }
  
  .nav-close-btn ion-icon {
  font-size: 20px;
  --ionicon-stroke-width: 45px;
  padding: 5px;
  }
  
  .navbar-link {
  color: var(--cadet);
  font-size: var(--fs-5);
  font-weight: var(--fw-600);
  text-transform: uppercase;
  padding-block: 15px;
  }
  
  .navbar-link:is(:hover, :focus) { color: var(--orange-soda); }
  
  .overlay {
  position: fixed;
  inset: 0;
  background: hsla(0, 0%, 0%, 0.7);
  opacity: 0;
  pointer-events: none;
  transition: var(--transition);
  z-index: 4;
  }
  
  .overlay.active {
  opacity: 1;
  pointer-events: all;
  }
  
  .header-bottom-actions {
  background: var(--white);
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  display: flex;
  justify-content: space-evenly;
  padding-block: 15px 10px;
  box-shadow: -2px 0 30px hsla(237, 71%, 52%, 0.2);
  z-index: 3;
  }
  
  .header-bottom-actions-btn ion-icon {
  color: hsl(0, 0%, 10%);
  font-size: 20px;
  margin-inline: auto;
  margin-bottom: 5px;
  --ionicon-stroke-width: 40px;
  transition: var(--transition);
  }
  
  .header-bottom-actions-btn:is(:hover, :focus) ion-icon { color: var(--orange-soda); }
  
  .header-bottom-actions-btn span {
  color: var(--cadet);
  font-family: var(--ff-poppins);
  font-size: var(--fs-7);
  font-weight: var(--fw-500);
  }
  
  
  
  /*-----------------------------------*\
  #HERO
  \*-----------------------------------*/
  
  .hero {
  background: var(--cultured-2);
  padding-block: var(--section-padding);
  }
  
  .hero-content { margin-bottom: 60px; }
  
  .hero-subtitle {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 5px;
  margin-bottom: 20px;
  }
  
  .hero-subtitle ion-icon { color: var(--orange-soda); }
  
  .hero-subtitle span {
  color: var(--dark-jungle-green);
  font-size: var(--fs-5);
  font-weight: var(--fw-700);
  }
  
  .hero-title { margin-bottom: 20px; }
  
  .hero-text {
  color: var(--cadet);
  font-size: var(--fs-5);
  line-height: 1.8;
  padding-left: 15px;
  border-left: 1px solid;
  margin-bottom: 30px;
  }
  
  
  
  
  /*-----------------------------------*\
  #MEDIA QUERIES
  \*-----------------------------------*/
  
  /**
  * responsive for larger than 600px screen
  */
  
  @media (min-width: 600px) {
  
  /**
   * CUSTOM PROPERTY
   */
  
  :root {
  
    /**
     * typography
     */
  
    --fs-2: 1.875rem;
  
  }
  
  
  
  /**
   * REUSED STYLE
   */
  
  .container {
    max-width: 550px;
    margin-inline: auto;
  }
  
  .has-scrollbar {
    gap: 25px;
    margin-inline: -25px;
    padding-inline: 25px;
    scroll-padding-left: 25px;
  }
  
  .has-scrollbar > li { min-width: calc(50% - 12.5px); }
  
}


  
  /**
  * responsive for larger than 768px screen
  */
  
  @media (min-width: 768px) {
  
  /**
   * CUSTOM PROPERTY
   */
  
  :root {
  
    /**
     * typography
     */
  
    --fs-1: 2.5rem;
    --fs-5: 0.938rem;
    --fs-6: 0.875rem;
  
  }
  
  
  
  /**
   * REUSED STYLE
   */
  
  .container { max-width: 720px; }
  
  .btn {
    --fs-5: 1rem;
    padding: 12px 28px;
  }
  
  
  
  /**
   * HEADER
   */
  
  .header-top { padding-block: 5px; }
  
  .header-top .wrapper { margin-left: auto; }
  
  .header-top-social-list { gap: 12px; }
  
  .header-top-social-link { font-size: 1rem; }
  
  .header-top-btn { padding: 10px 20px; }
  
  .header-bottom-actions {
    all: unset;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .header-bottom .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .header-bottom-actions-btn ion-icon { margin-bottom: 0; }
  
  .header-bottom-actions-btn span { display: none; }
  
  .header-bottom-actions-btn {
    background: var(--white);
    width: 50px;
    height: 50px;
    box-shadow: var(--shadow-2);
  }
  
  
  
  /**
   * HERO
   */
  
  .hero-content { max-width: 400px; }
  
  
  
  /**
   * ABOUT
   */
  
  .about .section-title { max-width: 30ch; }
  
  .about-text { max-width: 55ch }
  
  .about-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
  
  
  
  /**
   * FOOTER
   */
  
  .footer { margin-bottom: 0; }
  
  }
  
  
  
  
  
  /**
  * responsive for larger than 992px screen
  */
  
  @media (min-width: 992px) {
  
  /**
   * CUSTOM PROPERTY
   */
  
  :root {
  
    /**
     * typography
     */
  
    --fs-1: 3.125rem;
    --fs-4: 1.375rem;
  
  }
  
  
  
  /**
   * REUSED STYLE
   */
  
  .container { max-width: 970px; }
  
  .btn { padding: 15px 40px; }
  
  
  
  /**
   * HEADER
   */
  
  .header-top-list,
  .header-top .wrapper { gap: 30px; }
  
  
  
  /**
   * HERO
   */
  
  .hero-content {
    max-width: unset;
    margin-bottom: 0;
  }
  
  .hero .container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
  }
  
}


  
  
  /**
  * responsive for larger than 1200px screen
  */
  
  @media (min-width: 1200px) {
  
  /**
   * CUSTOM PROPERTY
   */
  
  :root {
  
    /**
     * typography
     */
  
    --fs-2: 2.75rem;
    --fs-4: 1.5rem;
  
  }
  
  
  
  /**
   * REUSED STYLE
   */
  
  .container { max-width: 1200px; }
  
  .has-scrollbar > li { min-width: calc(33.33% - 16.66px); }
  
  
  
  /**
   * HEADER
   */
  
  .header-bottom { padding-block: 30px; }
  
  .header-bottom-actions-btn:last-child,
  .navbar-top,
  .overlay { display: none; }
  
  .navbar,
  .navbar.active { all: unset; }
  
  .navbar-list {
    display: flex;
    align-items: center;
    gap: 30px;
  }
  
  .navbar-link {
    color: var(--dark-jungle-green);
    --fs-5: 1.125rem;
    text-transform: capitalize;
  }
  
  .header { padding-bottom: 114px; }
  
  .header-bottom {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
  }
  
  .header.active .header-bottom {
    position: fixed;
    bottom: auto;
    top: -94px;
    padding-block: 20px;
    box-shadow: 0 10px 50px hsla(237, 71%, 52%, 0.2);
    animation: slideDown 0.25s ease-out forwards;
  }
  
  @keyframes slideDown {
    0% { transform: translateY(0); }
    100% { transform: translateY(100%); }
  }
  
  







.auth-modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      z-index: 1001;
      display: none;
      max-width: 450px;
      width: 90%;
      overflow: hidden;
    }

    .auth-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      display: none;
    }

    .auth-container {
      padding: 2rem;
      display: none;
    }

    .auth-container.active {
      display: block;
    }

    .auth-title {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #2c3e50;
    }

    .auth-form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .auth-input-group {
      position: relative;
    }

    .auth-input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #7f8c8d;
    }

    .auth-input-group input {
      width: 100%;
      padding: 12px 15px 12px 40px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    .auth-input-group label {
      position: absolute;
      left: 40px;
      top: 12px;
      background: white;
      padding: 0 5px;
      color: #7f8c8d;
      transition: all 0.3s;
    }

    .auth-input-group input:focus + label,
    .auth-input-group input:not(:placeholder-shown) + label {
      top: -10px;
      font-size: 0.8rem;
      color: #3498db;
    }

    .auth-submit-btn {
      background: #3498db;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 1rem;
    }

    .auth-separator {
      display: flex;
      align-items: center;
      margin: 1rem 0;
      color: #7f8c8d;
    }

    .auth-separator::before,
    .auth-separator::after {
      content: "";
      flex: 1;
      border-bottom: 1px solid #ddd;
    }

    .auth-separator span {
      padding: 0 10px;
    }

    .auth-social-btns {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .auth-social-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 1px solid #ddd;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      background: white;
    }

    .auth-social-btn.google {
      color: #db4437;
    }

    .auth-social-btn.facebook {
      color: #4267B2;
    }

    .auth-switch {
      text-align: center;
      color: #7f8c8d;
    }

    .auth-switch-btn {
      background: none;
      border: none;
      color: #3498db;
      cursor: pointer;
      padding: 0;
    }

    .auth-forgot-link {
      text-align: right;
      color: #3498db;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .auth-close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #7f8c8d;
    }
  }











   















  





















  
  
  </style>
   <!-- 
    - ionicon link
  -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  </body>
</html>