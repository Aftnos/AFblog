// js/xx.js

document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.xx-toggle-button');
    const directoryNames = document.querySelectorAll('.xx-directory-name');

    // 添加点击事件到展开/收缩按钮
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const subContent = this.parentElement.querySelector('.xx-sub-content');
            if (subContent.classList.contains('show')) {
                subContent.classList.remove('show');
                this.innerHTML = '<i class="fas fa-plus"></i>';
            } else {
                subContent.classList.add('show');
                this.innerHTML = '<i class="fas fa-minus"></i>';
            }
        });
    });

    // 也允许点击目录名称来展开/收缩
    directoryNames.forEach(name => {
        name.addEventListener('click', function() {
            const parentLi = this.parentElement;
            const toggleButton = parentLi.querySelector('.xx-toggle-button');
            toggleButton.click(); // 触发按钮点击事件
        });
    });
});
