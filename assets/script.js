function renameForm() {
    var popup = document.querySelector('.popup-rename-file');
    popup.classList.remove('hide');
    var oldName = this.previousSibling.previousSibling.textContent;
    popup.childNodes[1].childNodes[4].value = oldName;
    popup.childNodes[1].childNodes[7].value = oldName;
    popup.childNodes[1].childNodes[9].value = this.getAttribute('data-path');
}

function main() {
    var btnRename = document.querySelectorAll('span.rename-file');
    for (var i = 0; i < btnRename.length; i++){
        btnRename[i].addEventListener("click", renameForm);
    }
}

window.onload = main;

