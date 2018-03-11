function renameForm() {
    var popup = document.querySelector('div.popup-rename');
    popup.classList.remove('hide');
    document.querySelector('div.overlay').classList.remove('hide');
    popup.classList.add('flex');
    var oldName = this.previousSibling.previousSibling.previousSibling.previousSibling.textContent;
    popup.childNodes[1].childNodes[4].value = oldName;
    popup.childNodes[1].childNodes[7].value = oldName;
    popup.childNodes[1].childNodes[9].value = this.getAttribute('data-path');
}

function deleteForm() {
    var popup = document.querySelector('div.popup-delete');
    popup.classList.remove('hide');
    document.querySelector('div.popup-delete').classList.add('flex');
    document.querySelector('div.overlay').classList.remove('hide');
    document.querySelector('span.delete-name').innerHTML = this.getAttribute('data-path');
    document.querySelector('input.delete-path').value = this.getAttribute('data-path');

    document.querySelector('button.close-delete').addEventListener("click", closeDelete);
}

function closeDelete() {
    document.querySelector('div.popup-delete').classList.add('hide');
    document.querySelector('div.overlay').classList.add('hide');
    document.querySelector('div.popup-delete').classList.remove('flex');
    return false;
}

function mooveForm() {
    document.querySelector('div.popup-moove').classList.remove('hide');
    document.querySelector('div.popup-moove').classList.add('flex');
    document.querySelector('div.overlay').classList.remove('hide');
    document.querySelector('input.input-hidden-moove').value = this.getAttribute('data-path');
}

function main() {
    var btnRename = document.querySelectorAll('span.rename');
    for (var i = 0; i < btnRename.length; i++){
        btnRename[i].addEventListener("click", renameForm);
    }
    var btnDelete = document.querySelectorAll('span.delete');
    for (var i = 0; i <btnDelete.length; i++){
        btnDelete[i].addEventListener("click", deleteForm);
    }

    var btnMoove = document.querySelectorAll('span.moove');
    for (var i =0; i <btnMoove.length; i++){
        btnMoove[i].addEventListener("click", mooveForm);
    }
}

window.onload = main;

