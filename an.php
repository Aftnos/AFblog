<style>
        .back-to-top{
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: rgba(26, 26, 26, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0; /* 初始完全透明 */
            visibility: hidden; /* 初始隐藏 */
            z-index: 1000;
            transition: opacity 0.5s ease, visibility 0.5s ease, background-color 0.5s ease-in-out, box-shadow 0.5s ease-in-out;
        }

        .an-icon{
            fill: #7d7d7d; 
            width: 20px;
            height: 20px;
            transition:fill 0.5s ease-in-out;
        }

        .back-to-top.show {
            opacity: 1; /* 显示时完全不透明 */
            visibility: visible; /* 显示时可见 */
        }

        .back-to-top:hover {
            box-shadow: 0 0 20px rgba(252, 205, 0, 0.8);
        }

        .back-to-top:hover .an-icon {
            fill: #fccd00; /* 让图标在父元素悬停时渐变 */
        }

        .back-to-top .icon path {
            fill: white;
            transition: fill 0.3s ease;
        }

        .back-to-top:hover .icon path {
            fill: black;
        }

        @media (max-width: 768px) {
            .back-to-top {
                width: 40px;
                height: 40px;
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
    

    <button id="backToTop" class="back-to-top">
        <svg t="1734769468657" class="an-icon" viewBox="0 0 1470 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1458">
            <path d="M664.965051 36.390373L20.010522 880.212016c-44.471546 58.191134-2.978155 142.114864 70.271066 142.114863h1289.8254c73.232489 0 114.725879-83.923729 70.271065-142.114863L805.473719 36.390373a88.441155 88.441155 0 0 0-140.508668 0z" p-id="1459"></path>
        </svg>
    </button>

    <script>
        const backToTopButton = document.getElementById("backToTop");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add("show");
            } else {
                backToTopButton.classList.remove("show");
            }
        });

        backToTopButton.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    </script>