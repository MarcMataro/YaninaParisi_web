<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centre de Psicologia - Yanina Parisi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estils.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Capçalera -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="img/logo.png" class="logo-nav" alt="Yanina Parisi" placeholder="Logo Yanina Parisi">
            </div>
            <nav>
                <ul>
                    <li><a href="#inici">Inici</a></li>
                    <li><a href="#serveis">Serveis</a></li>
                    <li><a href="#sobre-mi">Sobre mi</a></li>
                    <li><a href="#testimonis">Testimonis</a></li>
                    <li><a href="#contacte">Contacte</a></li>
                </ul>
            </nav>
            <a href="#contacte" class="btn">Demana una cita</a>
        </div>
    </header>

    <!-- Secció Hero -->
    <section class="hero" id="inici">
        <div class="container hero-content">
            <h1>Yanina Parisi psicologia</h1>
            <h2 class="heroh2">Benestar, suport i creixement personal.</h2>
            <div class="hero-buttons">
                <a href="#serveis" class="btn">Els meus serveis</a>
                <a href="#contacte" class="btn btn-light">Contacta'm</a>
            </div>
        </div>
    </section>

    <!-- Serveis -->
    <section id="serveis">
        <div class="container">
            <div class="section-title">
                <h2>Els meus serveis</h2>
                <p>Ofereixo teràpies personalitzades adaptades a les necessitats específiques de cada persona</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-heart service-icon"></i>
                    <h3>Teràpia individual</h3>
                    <p>Sessions individuals centrades en les teves necessitats específiques i objectius personals.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-users service-icon"></i>
                    <h3>Teràpia de parella</h3>
                    <p>Millora la comunicació i resoleu conflictes per enfortir la vostra relació.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-child service-icon"></i>
                    <h3>Teràpia infantil</h3>
                    <p>Ajuda especialitzada per a nens i adolescents que afronten desafiaments emocionals.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-stethoscope service-icon"></i>
                    <h3>Assessament psicològic</h3>
                    <p>Avaluació i diagnòstic per identificar i comprendre millor les teves necessitats.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-hands-helping service-icon"></i>
                    <h3>Teràpia familiar</h3>
                    <p>Millora les dinàmiques familiars i la comunicació entre els membres de la família.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-seedling service-icon"></i>
                    <h3>Teràpia online</h3>
                    <p>Sessions de teràpia per vídeo trucada des de la comoditat de casa teva.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sobre mi -->
    <section id="sobre-mi">
        <div class="container">
            <div class="section-title">
                <h2>Sobre mi</h2>
                <p>Connexió que va més enllà de la teràpia</p>
            </div>
            <div class="about-grid">
                <div class="about-content">
                    <h3>Yanina Parisi</h3>
                    <p>Sóc psicòloga col·legiada amb més de 10 anys d'experiència ajudant a persones a superar adversitats i trobar el seu camí cap al benestar mental.</p>
                    <p>La meva abordatge es centra en crear un espai segur i sense judicis on puguis explorar els teus pensaments i sentiments. Crec en la capacitat de cada persona per créixer i canviar, i em considero un acompanyant en el teu viatge cap a una vida més plena.</p>
                    <p>La meva formació inclou un Màster en Teràpia Cognitivo-Conductual i especialitzacions en teràpia de parella i teràpia infantil. Continuo formant-me regularment per oferir les tècniques més actuals i efectives.</p>
                    <a href="#contacte" class="btn">Programa una consulta</a>
                </div>
                <div class="about-image">
                    <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='500' height='400' viewBox='0 0 500 400'><rect width='500' height='400' fill='%236a9fb5'/><circle cx='250' cy='150' r='80' fill='%239f86c0'/><rect x='170' y='250' width='160' height='100' fill='%23a5c882'/></svg>" alt="Yanina Parisi - Psicòloga">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonis -->
    <section id="testimonis">
        <div class="container">
            <div class="section-title">
                <h2>Testimonis</h2>
                <p>El que diuen els meus pacients</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"La Yanina m'ha ajudat a superar la meva ansietat com mai havia imaginat. Les seves tècniques i suport em van donar les eines necessàries per afrontar les meves pors."</p>
                    <div class="testimonial-author">
                        <div class="author-image"><i class="fas fa-user"></i></div>
                        <div>
                            <h4>Laura G.</h4>
                            <p>Pacient des de 2021</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Després de poc més de sis mesos de teràpia de parella, la nostra relació ha millorat dràsticament. Gràcies a la Yanina per ensenyar-nos a comunicar-nos millor."</p>
                    <div class="testimonial-author">
                        <div class="author-image"><i class="fas fa-users"></i></div>
                        <div>
                            <h4>Marc i Elena</h4>
                            <p>Pacients des de 2022</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacte -->
    <section id="contacte">
        <div class="container">
            <div class="section-title">
                <h2>Contacte</h2>
                <p>No dubtis a posar-te en contacte amb mi</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt contact-icon"></i>
                        <div>
                            <h4>Adreça</h4>
                            <p>Carrer de la Pau, 23, Girona</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone contact-icon"></i>
                        <div>
                            <h4>Telèfon</h4>
                            <p>+34 972 123 45 67</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope contact-icon"></i>
                        <div>
                            <h4>Email</h4>
                            <p>yanina@psicologiayanina.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock contact-icon"></i>
                        <div>
                            <h4>Horari</h4>
                            <p>Dilluns a Divendres: 9:00 - 20:00</p>
                            <p>Dissabte: 10:00 - 14:00</p>
                        </div>
                    </div>
                </div>
                <form>
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" id="name" placeholder="El teu nom">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="El teu email">
                    </div>
                    <div class="form-group">
                        <label for="phone">Telèfon</label>
                        <input type="tel" id="phone" placeholder="El teu telèfon">
                    </div>
                    <div class="form-group">
                        <label for="message">Missatge</label>
                        <textarea id="message" placeholder="Com et puc ajudar?"></textarea>
                    </div>
                    <button type="submit" class="btn">Enviar missatge</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Peu de pàgina -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <img src="img/Logo2.png" class="logo-footer" alt="Psicòloga Yanina Parisi" placeholder="Logo psicòloga Yanina Parisi">
                    <p>Ajudant-te a trobar el teu camí cap al benestar mental i emocional.</p>
                </div>
                <div class="footer-column">
                    <h3>Enllaços ràpids</h3>
                    <ul>
                        <li><a href="#inici">Inici</a></li>
                        <li><a href="#serveis">Serveis</a></li>
                        <li><a href="#sobre-mi">Sobre mi</a></li>
                        <li><a href="#testimonis">Testimonis</a></li>
                        <li><a href="#contacte">Contacte</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Serveis</h3>
                    <ul>
                        <li><a href="#">Teràpia individual</a></li>
                        <li><a href="#">Teràpia de parella</a></li>
                        <li><a href="#">Teràpia infantil</a></li>
                        <li><a href="#">Teràpia online</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Xarxes socials</h3>
                    <ul>
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Psicologia Yanina. Tots els drets reservats.</p>
            </div>
        </div>
    </footer>

    <script>
        // Script per a la navegació suau
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>