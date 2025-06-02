import {Canvas, Control, Image, Object as FabricObject, Rect, util} from 'fabric';
import {v4 as uuidv4} from 'uuid';
import TomSelect from "tom-select/base";

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
    const select = document.getElementById('Event_scheme');
    const imageName = select.value;

    if (!imageName) {
        console.log('No image selected');
        return;
    }

    try {
        const response = await fetch(`/api/schemes/${imageName}`, {
            headers: {
                'X-API-KEY': '16777761'
            }
        });
        const data = await response.json();
        const img = await Image.fromURL(data.image, {crossOrigin: 'anonymous'});

        canvas.backgroundImage = img;
        canvas.setDimensions({width: img.width, height: img.height});
        canvas.backgroundColor = null;
        canvas.renderAll();
    } catch (error) {
        console.log('Failed to load background image');
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
    ctx.restore();
}

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

    // Вставляем значения в текстовые поля
    document.getElementById('infoPlaceName').textContent  = place.name ?? '—';
    document.getElementById('infoPlacePrice').textContent = place.price ?? '—';

    document.getElementById('infoSectionInput').value    = place.section ?? '';
    document.getElementById('infoRowInput').value        = place.row ?? '';
    document.getElementById('infoSeatNumberInput').value = place.seatNumber ?? '';

    const widthInput = document.getElementById('infoWidthInput');
    const heightInput = document.getElementById('infoHeightInput');

    widthInput.value  = Math.round(chair.getScaledWidth());
    heightInput.value = Math.round(chair.getScaledHeight());

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
    let placeData = places.find(p => p.id == chairType);

    const section = sectionInput.value.trim();
    const row     = rowInput.value.trim();
    const number  = numberInput.value.trim();

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
    const schemeDataField = document.getElementById('Event_schemeData');
    const data = canvas.getObjects().map(place => ({
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
        booked: place.placeData.booked ?? false,
        section:    place.placeData.section ?? null,
        row:        place.placeData.row ?? null,
        seatNumber: place.placeData.seatNumber ?? null,
    }));
    schemeDataField.value = JSON.stringify(data);
}

// Load objects from saved data
function loadObjects() {
    const schemeData = document.getElementById('Event_schemeData');

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

        canvas.add(chair);
        canvas.setActiveObject(chair);
        canvas.requestRenderAll();
    });
}

// Initialize application
document.addEventListener('DOMContentLoaded', async () => {
    const eventScheme = document.getElementById('Event_scheme');
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
            canvas.getObjects().map(obj => obj.placeData.placeId)
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

    const schemeSelect = document.getElementById('Event_scheme');
    const schemeDataField = document.getElementById('Event_schemeData');

    schemeSelect?.addEventListener('change', async (e) => {
        if (!schemeDataField || schemeDataField.value.trim() !== '' && schemeDataField.value !== 'null' && schemeDataField.value !== '[]') {
            console.log('Данные схемы уже есть — не загружаем повторно');
            return;
        }

        const schemeId = e.target.value;
        if (!schemeId) return;
        try {
            const res = await fetch(`/api/schemes/${schemeId}`, {
                headers: {
                    'X-API-KEY': '16777761'
                }
            });
            const json = await res.json();
            if (json.schemeData) {
                canvas.clear();

                schemeDataField.value = json.schemeData;
                console.log('Подгрузили данные схемы в Event_schemeData');
                loadObjects(); // отрисовать

                populatePlaceTypes();
            }
        } catch (err) {
            console.error('Ошибка загрузки schemeData:', err);
        }
    });

    eventScheme?.addEventListener('change', loadBackgroundImage);
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

    submitBtns.forEach(btn => btn.addEventListener('click', saveData));

    if (eventScheme.value) {
        await loadBackgroundImage();
        loadObjects();
    }

    const typeSelect  = document.getElementById('Event_type');
    const priceInput  = document.getElementById('Event_price');
    const placesInput = document.getElementById('Event_places');
    const schemeTabLi = document.querySelector('a[href="#tab-shema-zala"]').closest('li.nav-item');
    const priceField  = priceInput.closest('.form-group');
    const placesField = placesInput.closest('.form-group');

    function updateVisibility() {
        const type = typeSelect.value;
        schemeTabLi.style.display = type === 'Места согласно билетам' ? '' : 'none';
        priceField.style.display    = type === 'Места согласно билетам' ? 'none' : '';
        placesField.style.display   = (type === 'Места согласно билетам' || type === 'Неограниченное количество мест') ? 'none' : '';
    }

    function clearFields() {
        priceInput.value  = '';
        placesInput.value = '';
    }

    updateVisibility();
    typeSelect.addEventListener('change', () => {
        updateVisibility();
        clearFields();
    });
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

        const schemeDataField = document.getElementById('Event_schemeData');
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
                    active.set('placeData', { ...active.placeData });
                    const json = active.toJSON(['placeData', 'scaleX', 'scaleY', 'width', 'height']);
                    window._fabricClipboard = json;
                    console.log('Place copied:', json);
                } catch (err) {
                    console.error('Copy failed:', err);}
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

                    cloned.set({
                        left: (cloned.left || 0) + 20,
                        top: (cloned.top || 0) + 20,
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
