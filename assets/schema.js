import {Canvas, Control, Image, Object, Rect, util} from 'fabric';

const canvas = new Canvas('canvas', {
    backgroundColor: '#000000',
    selection: true
});

const deleteIcon = "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='439.067' r='218.516'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E";

const deleteImg = document.createElement('img');
deleteImg.src = deleteIcon;

Object.prototype.transparentCorners = false;
Object.prototype.cornerColor = 'blue';
Object.prototype.cornerStyle = 'circle';

async function loadBackgroundImage() {
    const select = document.getElementById('Event_scheme');
    const imageName = select.value;
    if (!imageName) {
        alert('No image selected');
        return;
    }
    const response = await fetch(`/api/schemes/${imageName}`);
    const data = await response.json();
    const img = await Image.fromURL(data.image, {crossOrigin: 'anonymous'});

    canvas.backgroundImage = img;
    canvas.setDimensions({ width: img.width, height: img.height });
    canvas.backgroundColor = null;
    canvas.renderAll();
}

function showChairModal(e) {
    e.preventDefault();
    const modal = document.getElementById('modal');
    if (modal) {
        modal.style.display = 'block';
        console.log('Modal opened');
    } else {
        console.error('Modal element not found');
    }
}

function renderIcon(ctx, left, top, _styleOverride, fabricObject) {
    const size = this.cornerSize;
    ctx.save();
    ctx.translate(left, top);
    ctx.rotate(util.degreesToRadians(fabricObject.angle));
    ctx.drawImage(deleteImg, -size / 2, -size / 2, size, size);
    ctx.restore();
}

function deleteObject(_eventData, transform) {
    const canvas = transform.target.canvas;
    canvas.remove(transform.target);
    canvas.requestRenderAll();
    console.log('Chair deleted via control');
    return true;
}

function closeModal(e) {
    e.preventDefault();
    const modal = document.getElementById('modal');
    if (modal) {
        modal.style.display = 'none';
        console.log('Modal closed');
    }
}

async function addChair(e) {
    e.preventDefault();
    const chairType = document.getElementById('chairType').value;
    let fillColor;

    switch (chairType) {
        case 'vip':
            fillColor = 'red';
            break;
        case 'standard':
            fillColor = 'blue';
            break;
        case 'accessible':
            fillColor = 'green';
            break;
        default:
            fillColor = 'blue';
    }

    console.log('Adding chair with color:', fillColor);

    const chair = new Rect({
        left: 50,
        top: 50,
        width: 30,
        height: 30,
        fill: fillColor,
        stroke: 'black',
        strokeWidth: 1,
        selectable: true,
        type: 'place',
        chairType: chairType,
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

    chair.on('selected', () => {
        console.log('Chair selected');
        chair.controls.deleteControl.visible = true;
        canvas.requestRenderAll();
    });
    chair.on('deselected', () => {
        console.log('Chair deselected');
        chair.controls.deleteControl.visible = false;
        canvas.requestRenderAll();
    });
    chair.on('moving', () => {
        console.log('Chair moving');
        canvas.requestRenderAll();
    });

    canvas.add(chair);
    canvas.setActiveObject(chair);
    canvas.requestRenderAll();
    closeModal({ preventDefault: () => {} });
}

function clearCanvas(e) {
    e.preventDefault();
    canvas.clear();
    canvas.backgroundColor = '#f0f0f0';
    canvas.backgroundImage = null;
    canvas.requestRenderAll();
}

function saveLayout(e) {
    e.preventDefault();
    const json = JSON.stringify(canvas.toJSON());
    console.log('Saved Layout:', json);
    alert('Layout saved to console as JSON!');
}

document.addEventListener('DOMContentLoaded', () => {
    const eventScheme = document.getElementById('Event_scheme');
    const addChairBtn = document.getElementById('addChairBtn');
    const clearCanvasBtn = document.getElementById('clearCanvasBtn');
    const saveLayoutBtn = document.getElementById('saveLayoutBtn');
    const confirmChairBtn = document.getElementById('confirmChairBtn');
    const cancelChairBtn = document.getElementById('cancelChairBtn');

    if (!eventScheme) console.error('Element #Event_scheme not found');
    if (!addChairBtn) console.error('Element #addChairBtn not found');
    if (!clearCanvasBtn) console.error('Element #clearCanvasBtn not found');
    if (!saveLayoutBtn) console.error('Element #saveLayoutBtn not found');
    if (!confirmChairBtn) console.error('Element #confirmChairBtn not found');
    if (!cancelChairBtn) console.error('Element #cancelChairBtn not found');

    eventScheme?.addEventListener('change', loadBackgroundImage);
    addChairBtn?.addEventListener('click', showChairModal);
    clearCanvasBtn?.addEventListener('click', clearCanvas);
    saveLayoutBtn?.addEventListener('click', saveLayout);
    confirmChairBtn?.addEventListener('click', addChair);
    cancelChairBtn?.addEventListener('click', closeModal);

    // Constrain objects within canvas boundaries
    canvas.on('object:moving', function(e) {
        const obj = e.target;
        obj.setCoords();
        const br = obj.getBoundingRect(true);

        if (br.left < 0) {
            obj.left -= br.left;
        }
        if (br.top < 0) {
            obj.top -= br.top;
        }
        if (br.left + br.width > canvas.getWidth()) {
            obj.left -= (br.left + br.width - canvas.getWidth());
        }
        if (br.top + br.height > canvas.getHeight()) {
            obj.top -= (br.top + br.height - canvas.getHeight());
        }

        canvas.requestRenderAll();
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Delete' && canvas.getActiveObject()) {
        const activeObject = canvas.getActiveObject();
        if (activeObject.type === 'place') {
            canvas.remove(activeObject);
            canvas.requestRenderAll();
            console.log('Chair deleted via Delete key');
        }
    }
});
