import {Canvas, Control, Image, Object as FabricObject, Rect, util} from 'fabric';
import {v4 as uuidv4} from 'uuid';

const canvas = new Canvas('canvas', {
    backgroundColor: '#000000',
    selection: true
});

let places = [];

const deleteIcon = "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='439.067' r='218.516'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E";
const infoIcon = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Ccircle cx='12' cy='12' r='12' fill='%23007BFF'/%3E%3Ctext x='12' y='16' font-family='Arial' font-size='14' fill='white' text-anchor='middle' dominant-baseline='middle'%3Ei%3C/text%3E%3C/svg%3E";

const deleteImg = document.createElement('img');
deleteImg.src = deleteIcon;
const infoImg = document.createElement('img');
infoImg.src = infoIcon;

FabricObject.prototype.transparentCorners = false;
FabricObject.prototype.cornerColor = 'blue';
FabricObject.prototype.cornerStyle = 'circle';
FabricObject.prototype.toObject = (function (toObject) {
    return function (propertiesToInclude) {
        return toObject.call(this, ['placeData', ...propertiesToInclude]);
    };
})(FabricObject.prototype.toObject);

async function loadBackgroundImage() {
    const hiddenImageInput = document.getElementById('Scheme_image');
    const fileInput = document.getElementById('Scheme_imageFile_file');
    if (hiddenImageInput && hiddenImageInput.value) {
        var url = `/images/scheme/${hiddenImageInput.value}`;
        console.log('Загрузка схемы по пути:', url);
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                console.log('Выбран локальный файл:', file.name);
                const reader = new FileReader();
                reader.onload = async function (e) {
                    const url = e.target.result;
                    try {
                        const img = await Image.fromURL(url);
                        canvas.backgroundImage = img;
                        canvas.setDimensions({ width: img.width, height: img.height });
                        canvas.backgroundColor = null;
                        canvas.renderAll();
                    } catch (error) {
                        alert('Не удалось отобразить выбранное изображение');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    try {
        const img = await Image.fromURL(url, {crossOrigin: 'anonymous'});

        canvas.backgroundImage = img;
        canvas.setDimensions({width: img.width, height: img.height});
        canvas.backgroundColor = null;
        canvas.renderAll();
    } catch (error) {
        alert('Failed to load background image');
    }
}

// Show chair selection modal
function showChairModal(e) {
    e.preventDefault();
    const modalElement = document.getElementById('placeSelectModal');
    if (!modalElement) return;

    const select = document.getElementById('placesSelect');
    select.innerHTML = '';
    places.forEach(place => {
        select.insertAdjacentHTML(
            'beforeend',
            `<option value="${place.id}" data-color="${place.color}">${place.name}</option>`
        );
    });

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function renderIcon(ctx, left, top, _styleOverride, fabricObject) {
    const size = this.cornerSize;
    ctx.save();
    ctx.translate(left, top);
    ctx.rotate(util.degreesToRadians(fabricObject.angle));
    ctx.drawImage(deleteImg, -size / 2, -size / 2, size, size);
    ctx.restore();
}

function renderInfo(ctx, left, top, _styleOverride, fabricObject) {
    const size = this.cornerSize;
    ctx.save();
    ctx.translate(left, top);
    ctx.rotate(util.degreesToRadians(fabricObject.angle));
    ctx.drawImage(infoImg, -size / 2, -size / 2, size, size);
    ctx.restore();}

function deleteObject(_eventData, transform) {
    const canvas = transform.target.canvas;
    canvas.remove(transform.target);
    canvas.requestRenderAll();
    return true;
}

// Show chair info modal
function showInfoModalForChair(_eventData, transform) {
    const chair = transform.target;
    const place = places.find(p => p.id == chair.placeData.placeId);
    const modalElement = document.getElementById('infoModal');

    if (!modalElement || !place) {
        alert('Info modal or place data not found');
        return false;
    }

    const infoContent = document.getElementById('infoContent');
    if (infoContent) {
        infoContent.innerHTML = `
            <p><strong>Тип стула:</strong> ${place.name}</p>
            <p><strong>Цена:</strong> ${place.price}</p>
        `;
    }

    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });
    modal.show();
    return true;
}

// Add new chair to canvas
async function addChair(e) {
    e.preventDefault();
    const select = document.getElementById('placesSelect');
    const chairType = select.value;
    const option = select.querySelector(`option[value="${chairType}"]`);
    const fillColor = option.getAttribute('data-color');
    let placeData = places.find(p => p.id == chairType)

    const chair = new Rect({
        left: 50,
        top: 50,
        width: 30,
        height: 30,
        opacity: 0.5,
        fill: fillColor,
        selectable: true,
        type: 'place',
        placeData: {
            placeId: placeData.id,
            uuid: uuidv4(),
            name: placeData.name,
            price: placeData.price,
            color: placeData.color,
            booked: placeData.booked
        },
        objectCaching: false
    });

    chair.controls.deleteControl = new Control({
        x: 0.5,
        y: -0.5,
        offsetY: -16,
        offsetX: 16,
        cursorStyle: 'pointer',
        mouseUpHandler: deleteObject,
        render: renderIcon,
        cornerSize: 24
    });

    chair.controls.infoControl = new Control({
        x: -0.5,
        y: -0.5,
        offsetY: -16,
        offsetX: -16,
        cursorStyle: 'pointer',
        mouseUpHandler: showInfoModalForChair,
        render: renderInfo,
        cornerSize: 24
    });

    chair.on('selected', () => {
        chair.controls.deleteControl.visible = true;
        canvas.requestRenderAll();
    });

    chair.on('deselected', () => {
        chair.controls.deleteControl.visible = false;
        canvas.requestRenderAll();
    });

    chair.on('moving', () => canvas.requestRenderAll());

    canvas.add(chair);
    canvas.setActiveObject(chair);
    canvas.requestRenderAll();

    const modalElement = document.getElementById('placeSelectModal');
    if (modalElement) {
        bootstrap.Modal.getInstance(modalElement).hide();
    }
}

// Save canvas data
function saveData() {
    const schemeDataField = document.getElementById('Scheme_schemeData');
    const data = canvas.getObjects().map(place => ({
        placeId: place.placeData.placeId,
        uuid: place.placeData.uuid,
        name: place.placeData.name,
        price: place.placeData.price,
        color: place.placeData.color,
        cords: place.getCoords(),
        left: place.left,
        top: place.top,
        width: place.getScaledWidth(),
        height: place.getScaledHeight(),
        booked: place.placeData.booked ?? false,
    }));
    schemeDataField.value = JSON.stringify(data);
}

// Load objects from saved data
function loadObjects() {
    const schemeData = document.getElementById('Scheme_schemeData');

    if (!schemeData || !schemeData.value || schemeData.value.trim() === '' || schemeData.value === 'null') return;

    JSON.parse(schemeData.value).forEach(place => {
        const isBooked = place.booked === true;

        const chair = new Rect({
            left: place.left,
            top: place.top,
            width: place.width,
            height: place.height,
            opacity: 0.5,
            fill: isBooked ? '#999999' : place.color,
            selectable: !isBooked,
            evented: !isBooked,
            hasControls: !isBooked,
            hasBorders: !isBooked,
            lockMovementX: isBooked,
            lockMovementY: isBooked,
            type: 'place',
            placeData: {
                placeId: place.placeId,
                uuid: place.uuid,
                name: place.name,
                price: place.price,
                color: place.color,
                booked: isBooked
            },
            objectCaching: false
        });

        if (isBooked) {
            chair.evented = true;
            chair.selectable = false;
            chair.on('mousedown', () => {
                const modal = new bootstrap.Modal(document.getElementById('bookedWarningModal'));
                modal.show();
            });
        }

        chair.controls.deleteControl = new Control({
            x: 0.5,
            y: -0.5,
            offsetY: -16,
            offsetX: 16,
            cursorStyle: 'pointer',
            mouseUpHandler: deleteObject,
            render: renderIcon,
            cornerSize: 24
        });

        chair.controls.infoControl = new Control({
            x: -0.5,
            y: -0.5,
            offsetY: -16,
            offsetX: -16,
            cursorStyle: 'pointer',
            mouseUpHandler: showInfoModalForChair,
            render: renderInfo,
            cornerSize: 24
        });

        chair.on('selected', () => {
            chair.controls.deleteControl.visible = true;
            canvas.requestRenderAll();
        });

        chair.on('deselected', () => {
            chair.controls.deleteControl.visible = false;
            canvas.requestRenderAll();
        });

        chair.on('moving', () => canvas.requestRenderAll());

        canvas.add(chair);
        canvas.setActiveObject(chair);
        canvas.requestRenderAll();
    });
}

// Initialize application
document.addEventListener('DOMContentLoaded', async () => {
    const hiddenImageInput = document.getElementById('Scheme_image');
    const fileInput = document.getElementById('Scheme_imageFile_file');
    const addChairBtn = document.getElementById('addChairBtn');
    const confirmObjectBtn = document.getElementById('confirmObject');
    const cancelObjectBtn = document.getElementById('cancelObject');
    const modal = document.getElementById('placeSelectModal');
    const submitBtns = document.querySelectorAll('button[type="submit"]');

    Object.assign(modal, {
        className: 'modal fade',
        id: 'placeSelectModal',
        tabindex: '-1',
        ariaLabelledby: 'placeSelectModalLabel',
        ariaHidden: 'true'
    });

    places = await (await fetch('/api/places')).json();

    if (hiddenImageInput || fileInput) {
        await loadBackgroundImage();
        loadObjects();
    }
    addChairBtn?.addEventListener('click', showChairModal);
    confirmObjectBtn?.addEventListener('click', addChair);
    cancelObjectBtn?.addEventListener('click', () => {
        const modalElement = document.getElementById('placeSelectModal');
        if (modalElement) {
            bootstrap.Modal.getInstance(modalElement).hide();
        }
    });

    canvas.on('object:moving', (e) => {
        const obj = e.target;
        obj.setCoords();
        const br = obj.getBoundingRect(true);

        if (br.left < 0) obj.left -= br.left;
        if (br.top < 0) obj.top -= br.top;
        if (br.left + br.width > canvas.getWidth()) {
            obj.left -= (br.left + br.width - canvas.getWidth());
        }
        if (br.top + br.height > canvas.getHeight()) {
            obj.top -= (br.top + br.height - canvas.getHeight());
        }

        canvas.requestRenderAll();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Delete' && canvas.getActiveObject()) {
            const activeObject = canvas.getActiveObject();
            if (activeObject.type === 'place') {
                canvas.remove(activeObject);
                canvas.requestRenderAll();
            }
        }
    });

    submitBtns.forEach(btn => btn.addEventListener('click', saveData));
});