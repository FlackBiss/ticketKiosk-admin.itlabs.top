import {Canvas, Control, Image, Object as FabricObject, Rect, util} from 'fabric';
import {v4 as uuidv4} from 'uuid';
import * as fabric from "fabric";

const canvas = new Canvas('canvas', {
    backgroundColor: '#000000',
    selection: false
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

// Функция для установки контролов и событий на объект кресла
function setupChairControls(chair) {
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
}

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

function populatePlaceTypes() {
    const placeTypeSelect = document.getElementById('placeTypeSelect');
    if (!placeTypeSelect) return;

    placeTypeSelect.innerHTML = '<option value="">Выберите тип места</option>';

    const usedPlaceIds = new Set(
        canvas.getObjects()
            .filter(obj => obj.placeData && obj.placeData.placeId !== undefined)
            .map(obj => obj.placeData.placeId)
    );

    places.forEach(place => {
        if (usedPlaceIds.has(place.id)) {
            placeTypeSelect.insertAdjacentHTML('beforeend',
                `<option value="${place.id}" data-price="${place.price}">${place.name}</option>`);
        }
    });
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

function showInfoModalForChair(_eventData, transform) {
    const chair = transform.target;
    const place = chair.placeData;
    const modalElement = document.getElementById('infoModal');

    if (!modalElement || !place) return false;

    // Заполняем селектор типов мест
    const typeSelect = document.getElementById('infoPlaceTypeSelect');
    if (typeSelect) {
        typeSelect.innerHTML = '';
        places.forEach(p => {
            const selected = p.id === place.placeId ? 'selected' : '';
            typeSelect.insertAdjacentHTML(
                'beforeend',
                `<option value="${p.id}" data-price="${p.price}" data-color="${p.color}" ${selected}>${p.name}</option>`
            );
        });

        // Обновляем цену при смене выбора
        typeSelect.addEventListener('change', () => {
            const selectedId = typeSelect.value;
            const selectedPlace = places.find(pl => pl.id == selectedId);
            const priceSpan = document.getElementById('infoPlacePrice');
            if (priceSpan) {
                priceSpan.textContent = selectedPlace ? selectedPlace.price : '—';
            }
        });
    }

    // Устанавливаем цену текущего типа
    const priceSpan = document.getElementById('infoPlacePrice');
    if (priceSpan) {
        priceSpan.textContent = place.price ?? '—';
    }

    // Заполняем остальные поля
    document.getElementById('infoSectionInput').value    = place.section ?? '';
    document.getElementById('infoRowInput').value        = place.row ?? '';
    document.getElementById('infoSeatNumberInput').value = place.seatNumber ?? '';

    document.getElementById('infoWidthInput').value  = Math.round(chair.getScaledWidth());
    document.getElementById('infoHeightInput').value = Math.round(chair.getScaledHeight());

    canvas.setActiveObject(chair);

    bootstrap.Modal.getOrCreateInstance(modalElement).show();

    return true;
}

// Add new chair to canvas
async function addChair(e) {
    e.preventDefault();

    const sectionInput = document.getElementById('seatSectionInput');
    const rowInput     = document.getElementById('seatRowInput');
    const numberInput  = document.getElementById('seatNumberInput');

    const select = document.getElementById('placesSelect');
    const chairType = select.value;
    const option = select.querySelector(`option[value="${chairType}"]`);
    const fillColor = option.getAttribute('data-color');
    let placeData = places.find(p => p.id == chairType)

    const section = sectionInput.value.trim();
    const row     = rowInput.value.trim();
    const number  = numberInput.value.trim();

    const chair = new Rect({
        left: 50,
        top: 50,
        width: 30,
        height: 30,
        opacity: 0.5,
        strokeWidth: 0,
        fill: fillColor,
        selectable: true,
        type: 'place',
        placeData: {
            placeId: placeData.id,
            uuid: uuidv4(),
            name: placeData.name,
            price: placeData.price,
            color: placeData.color,
            booked: placeData.booked,
            section:    section,
            row:        row,
            seatNumber: number
        },
        objectCaching: false
    });

    setupChairControls(chair);

    canvas.add(chair);
    canvas.setActiveObject(chair);
    canvas.requestRenderAll();

    const modalElement = document.getElementById('placeSelectModal');
    if (modalElement) {
        bootstrap.Modal.getInstance(modalElement).hide();
    }

    const alreadyExists = [...placeTypeSelect.options].some(opt => opt.value == placeData.id);
    if (!alreadyExists) {
        const optionHtml = `<option value="${placeData.id}" data-price="${placeData.price}">${placeData.name}</option>`;
        placesSelect.insertAdjacentHTML('beforeend', optionHtml);      // селектор в модалке добавления
        placeTypeSelect.insertAdjacentHTML('beforeend', optionHtml);   // селектор изменения цены
    }
}

// Save canvas data
function saveData() {
    const schemeDataField = document.getElementById('Scheme_schemeData');
    if (!schemeDataField) {
        console.warn('Element with id "Event_schemeData" not found.');
        return;
    }

    const data = canvas.getObjects()
        .filter(place => place.placeData)
        .map(place => ({
            placeId: place.placeData.placeId,
            uuid: place.placeData.uuid,
            name: place.placeData.name,
            price: place.placeData.price,
            color: place.placeData.color,
            cords: place.getCoords(),
            left: place.left,
            top: place.top,
            width: place.width,
            height: place.height,
            scaleX: place.scaleX || 1,
            scaleY: place.scaleY || 1,
            strokeWidth: 0,
            booked: place.placeData.booked ?? false,
            section: place.placeData.section ?? null,
            row: place.placeData.row ?? null,
            seatNumber: place.placeData.seatNumber ?? null,
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
            scaleX: place.scaleX || 1,
            scaleY: place.scaleY || 1,
            strokeWidth: 0,
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
                booked: isBooked,
                section:    place.section,
                row:        place.row,
                seatNumber: place.seatNumber
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

        setupChairControls(chair);

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

    const placeTypeSelect = document.getElementById('placeTypeSelect');
    const placeTypePrice = document.getElementById('placeTypePrice');
    const applyPriceBtn = document.getElementById('applyPriceBtn');

    // Заполняем селект типами мест
    function populatePlaceTypes() {
        placeTypeSelect.innerHTML = '<option value="">Выберите тип места</option>';

        const usedPlaceIds = new Set(
            canvas.getObjects()
                .filter(obj => obj.placeData && obj.placeData.placeId !== undefined)
                .map(obj => obj.placeData.placeId)
        );

        places.forEach(place => {
            if (usedPlaceIds.has(place.id)) {  // Только типы, которые используются на холсте
                placeTypeSelect.insertAdjacentHTML('beforeend',
                    `<option value="${place.id}" data-price="${place.price}">${place.name}</option>`);
            }
        });
    }


    // Подстановка текущей цены при выборе типа
    placeTypeSelect.addEventListener('change', (e) => {
        const selectedOption = e.target.selectedOptions[0];
        placeTypePrice.value = selectedOption.dataset.price || '';
    });

    // Применение новой цены ко всем объектам выбранного типа
    applyPriceBtn.addEventListener('click', () => {
        const typeId = placeTypeSelect.value;
        const newPrice = parseFloat(placeTypePrice.value);

        if (!typeId || isNaN(newPrice)) {
            alert('Выберите тип и введите корректную цену.');
            return;
        }

        // Обновляем цену в массиве типов
        const placeType = places.find(p => p.id == typeId);
        if (placeType) placeType.price = newPrice;

        // Обновляем цену на canvas
        canvas.getObjects().forEach(obj => {
            if (obj.placeData.placeId == typeId) {
                obj.placeData.price = newPrice;
            }
        });

        canvas.requestRenderAll(); // Обновить отрисовку
        saveData(); // 🔁 Сохраняем новое состояние в поле schemeData

        alert(`Цена для всех мест типа "${placeType.name}" обновлена до ${newPrice}.`);
    });

    places = await (await fetch('/api/places', {
        headers: {
            'X-API-KEY': '16777761'
        }
    })).json();

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

    let rulerRect = null;
    let rulerOrientation = 'horizontal'; // 'horizontal' или 'vertical'

    function sendToBack(obj) {
        canvas.remove(obj);
        canvas._objects.unshift(obj);
        canvas.requestRenderAll();
    }

    function createRuler(orientation = 'horizontal') {
        if (rulerRect) {
            canvas.remove(rulerRect);
            rulerRect = null;
        }
        rulerOrientation = orientation;

        if (orientation === 'horizontal') {
            rulerRect = new fabric.Rect({
                left: 0,
                top: 100,
                width: canvas.getWidth() * 0.8,
                height: 16,
                fill: 'rgba(200,0,0,0.3)',
                selectable: true,
                objectCaching: false,
                hasControls: true,
                lockMovementX: false,
                lockMovementY: false,
                lockScalingX: false,
                lockScalingY: false,
                lockRotation: false,
                hoverCursor: 'move',
            });
        } else {
            // вертикальная линейка
            rulerRect = new fabric.Rect({
                left: 100,
                top: 0,
                width: 16,
                height: canvas.getHeight() * 0.8,
                fill: 'rgba(0,0,200,0.3)',
                selectable: true,
                objectCaching: false,
                hasControls: true,
                lockMovementX: false,
                lockMovementY: false,
                lockScalingX: false,
                lockScalingY: false,
                lockRotation: false,
                hoverCursor: 'move',
            });
        }

        canvas.add(rulerRect);
        sendToBack(rulerRect);
        canvas.setActiveObject(rulerRect);
        canvas.requestRenderAll();
    }

    function removeRuler() {
        if (!rulerRect) return;
        canvas.remove(rulerRect);
        rulerRect = null;
        canvas.requestRenderAll();
    }

// Кнопки на странице
    const toggleRulerBtn = document.getElementById('toggleRulerBtn');
    const toggleOrientationBtn = document.getElementById('toggleOrientationBtn');

    toggleRulerBtn?.addEventListener('click', () => {
        if (rulerRect) {
            removeRuler();
            toggleRulerBtn.textContent = 'Показать линейку';
            toggleOrientationBtn.disabled = true;
        } else {
            createRuler(rulerOrientation);
            toggleRulerBtn.textContent = 'Скрыть линейку';
            toggleOrientationBtn.disabled = false;
        }
    });

    toggleOrientationBtn?.addEventListener('click', () => {
        if (!rulerRect) return;
        if (rulerOrientation === 'horizontal') {
            createRuler('vertical');
            toggleOrientationBtn.textContent = 'Горизонтальная линейка';
        } else {
            createRuler('horizontal');
            toggleOrientationBtn.textContent = 'Вертикальная линейка';
        }
    });

// Обработчик движения объектов, ограничивающий пересечение с линейкой
    canvas.on('object:moving', (e) => {
        const obj = e.target;
        if (!rulerRect || !obj || obj === rulerRect) return;
        if (typeof obj.getBoundingRect !== 'function' || typeof rulerRect.getBoundingRect !== 'function') return;

        obj.setCoords();
        rulerRect.setCoords();

        const objBR = obj.getBoundingRect(true);
        const rulerBR = rulerRect.getBoundingRect(true);

        // Ограничение по краям канваса
        if (objBR.left < 0) obj.left -= objBR.left;
        if (objBR.top < 0) obj.top -= objBR.top;
        if (objBR.left + objBR.width > canvas.getWidth()) obj.left -= (objBR.left + objBR.width - canvas.getWidth());
        if (objBR.top + objBR.height > canvas.getHeight()) obj.top -= (objBR.top + objBR.height - canvas.getHeight());

        // Проверяем пересечение с линейкой в зависимости от ориентации
        if (rulerOrientation === 'horizontal') {
            const intersectsHorizontally = (objBR.left < rulerBR.left + rulerBR.width) && (objBR.left + objBR.width > rulerBR.left);
            const intersectsVertically = (objBR.top < rulerBR.top + rulerBR.height) && (objBR.top + objBR.height > rulerBR.top);

            if (intersectsHorizontally && intersectsVertically) {
                if (objBR.top < rulerBR.top) {
                    obj.top -= (objBR.top + objBR.height - rulerBR.top);
                } else {
                    obj.top += (rulerBR.top + rulerBR.height - objBR.top);
                }
                obj.setCoords();
            }
        } else {
            // вертикальная линейка - ограничение по горизонтали
            const intersectsHorizontally = (objBR.left < rulerBR.left + rulerBR.width) && (objBR.left + objBR.width > rulerBR.left);
            const intersectsVertically = (objBR.top < rulerBR.top + rulerBR.height) && (objBR.top + objBR.height > rulerBR.top);

            if (intersectsHorizontally && intersectsVertically) {
                if (objBR.left < rulerBR.left) {
                    obj.left -= (objBR.left + objBR.width - rulerBR.left);
                } else {
                    obj.left += (rulerBR.left + rulerBR.width - objBR.left);
                }
                obj.setCoords();
            }
        }

        canvas.requestRenderAll();
    });

    submitBtns.forEach(btn => btn.addEventListener('click', saveData));

    populatePlaceTypes();

    const clearSchemeBtn = document.getElementById('clearSchemeBtn');
    clearSchemeBtn?.addEventListener('click', () => {
        if (!confirm('Вы уверены, что хотите полностью очистить схему? Это действие нельзя отменить.')) return;

        canvas.clear();

        const placeTypeSelect = document.getElementById('placeTypeSelect');
        const placesSelect = document.getElementById('placesSelect');
        if (placeTypeSelect) placeTypeSelect.innerHTML = '<option value="">Выберите тип места</option>';
        if (placesSelect) placesSelect.innerHTML = '';

        const clearButton = document.querySelector('.ts-control .clear-button');
        if (clearButton) {
            clearButton.click();
        }

        const schemeDataField = document.getElementById('Scheme_schemeData');
        if (schemeDataField) schemeDataField.value = '';
    });

    const placesSelect = document.getElementById('placesSelect');
    const seatDetails  = document.getElementById('seatDetails');
    const sectionInput = document.getElementById('seatSectionInput');
    const rowInput     = document.getElementById('seatRowInput');
    const numberInput  = document.getElementById('seatNumberInput');
    const placeModalEl = document.getElementById('placeSelectModal');

    placeModalEl.addEventListener('show.bs.modal', () => {
        placesSelect.value = '';
        if (seatDetails) seatDetails.classList.add('d-none');
        if (sectionInput) sectionInput.value = '';
        if (rowInput) rowInput.value = '';
        if (numberInput) numberInput.value = '';
    });
    placesSelect.addEventListener('change', () => {
        if (!seatDetails) return;
        if (placesSelect.value) {
            seatDetails.classList.remove('d-none');
        } else {
            seatDetails.classList.add('d-none');
        }
    });

    applyPlaceChangesBtn?.addEventListener('click', () => {
        const chair = canvas.getActiveObject();
        if (!chair || !chair.placeData) return;

        const section    = document.getElementById('infoSectionInput').value.trim();
        const row        = document.getElementById('infoRowInput').value.trim();
        const seatNumber = document.getElementById('infoSeatNumberInput').value.trim();
        const width      = parseFloat(document.getElementById('infoWidthInput').value);
        const height     = parseFloat(document.getElementById('infoHeightInput').value);

        const typeSelect = document.getElementById('infoPlaceTypeSelect');
        const selectedPlace = places.find(p => p.id == typeSelect.value);

        if (selectedPlace) {
            chair.placeData.placeId = selectedPlace.id;
            chair.placeData.name    = selectedPlace.name;
            chair.placeData.price   = selectedPlace.price;
            chair.placeData.color   = selectedPlace.color;
            chair.set({ fill: selectedPlace.color });
        }

        chair.placeData.section    = section;
        chair.placeData.row        = row;
        chair.placeData.seatNumber = seatNumber;

        let newWidth = chair.width;
        let newHeight = chair.height;
        if (!isNaN(width) && width > 0) newWidth = Math.round(width);
        if (!isNaN(height) && height > 0) newHeight = Math.round(height);

        chair.set({
            scaleX: 1,
            scaleY: 1,
            width: newWidth,
            height: newHeight
        });

        chair.setCoords();
        canvas.requestRenderAll();
        saveData();

        // Обновляем селектор с типами мест, чтобы отобразить новую цену
        populatePlaceTypes();

        bootstrap.Modal.getInstance(document.getElementById('infoModal'))?.hide();
    });

    canvas.on('selection:created', (e) => {
        const sel = e.target;
        if (sel && sel.type === 'activeSelection') {
            canvas.discardActiveObject();
            canvas.requestRenderAll();
        }
    });

    document.addEventListener('keydown', async (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c') {
            const active = canvas.getActiveObject();
            console.log('Ctrl+C pressed, active object:', active);
            if (active && active.placeData) {
                try {
                    // Сохраняем сериализованный объект с placeData
                    const json = active.toJSON(['placeData']);
                    window._fabricClipboard = json;
                    console.log('Place copied:', json);
                } catch (err) {
                    console.error('Copy failed:', err);
                }
            } else {
                console.log('No valid active object to copy');
            }
        }

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v') {
            e.preventDefault();
            if (window._fabricClipboard) {
                try {
                    // enlivenObjects принимает массив, оборачиваем
                    const objects = await util.enlivenObjects([window._fabricClipboard]);
                    if (!objects || objects.length === 0) {
                        console.log('No objects enlivened');
                        return;
                    }
                    const cloned = objects[0];

                    // Генерируем новый uuid, создаём глубокую копию placeData
                    cloned.placeData = {...cloned.placeData, uuid: uuidv4()};

                    // Сбросим scaleX/Y, чтобы ширина/высота были корректны
                    cloned.set({
                        left: (cloned.left || 0) + 20,
                        top: (cloned.top || 0) + 20,
                        scaleX: 1,
                        scaleY: 1,
                        evented: true,
                        selectable: true,
                        objectCaching: false
                    });

                    setupChairControls(cloned);

                    canvas.add(cloned);
                    canvas.setActiveObject(cloned);
                    canvas.requestRenderAll();
                    saveData();
                    console.log('Place pasted', cloned);
                } catch (err) {
                    console.error('Paste failed:', err);
                }
            } else {
                console.log('Clipboard empty');
            }
        }
    });
});