<footer>
    <div class="footerContainer">
        <div class="socialIcons">
            <a href=""><i class="fa-brands fa-facebook"></i></a>
            <a href=""><i class="fa-brands fa-instagram"></i></a>
            <a href=""><i class="fa-brands fa-twitter"></i></a>
            <a href=""><i class="fa-brands fa-youtube"></i></a>
        </div>
        <div class="footerNav">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contactus.php">Contact Us</a></li>
            </ul>
        </div>

    </div>
    <div class="footerBottom">
        <p>Copyright &copy;2024 <strong><span class="designer">Peter Beans</span> </strong>All Rights Reserved</p>
    </div>
</footer>
<style>
    footer {
        background-color: #c9c9a6;
        border-radius: 15px;
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12);
        padding: 50px 30px;
        text-align: center;
    }

    .footerContainer {
        max-width: 1000px;
        margin: 0 auto;
    }

    .socialIcons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .socialIcons a {
        width: 60px;
        height: 60px;
        background-color: #9e9b76;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.3s ease-in-out, transform 0.3s ease-in-out;
    }

    .socialIcons a i {
        font-size: 24px;
    }

    .socialIcons a:hover {
        background-color: #7c4a32;
        transform: scale(1.1);
    }

    .footerNav {
        margin: 30px 0;
    }

    .footerNav ul {
        display: flex;
        justify-content: center;
        gap: 25px;
        list-style: none;
        padding: 0;
    }

    .footerNav ul li a {
        color: #fff;
        text-decoration: none;
        font-size: 18px;
        font-weight: bold;
        transition: color 0.3s ease-in-out;
    }

    .footerNav ul li a:hover {
        color: #7c4a32;
        text-decoration: underline;
    }

    .footerBottom {
        margin-top: 30px;
        font-size: 14px;
        color: #29412a;
    }

    .footerBottom p {
        margin-top: 20px;
        font-size: 20px;
        margin: 0;
    }

    .footerBottom .designer {
        color: #fff;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 25px;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>