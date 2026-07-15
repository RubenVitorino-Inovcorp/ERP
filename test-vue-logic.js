const form = { permissions: [] };
const modulesMap = { clientes: 'Clientes' };
const actions = ['read', 'create', 'update', 'delete'];

const matrix = {};
Object.keys(modulesMap).forEach(mod => {
    matrix[mod] = { label: modulesMap[mod], actions: {} };
    actions.forEach(act => {
        matrix[mod].actions[act] = `${mod}.${act}`;
    });
});

console.log("Matrix:", matrix);

function toggleModuleAll(mod, checked) {
    const modActions = Object.values(matrix[mod].actions);
    if (checked) {
        const newPerms = new Set(form.permissions);
        modActions.forEach(p => newPerms.add(p));
        form.permissions = Array.from(newPerms);
    } else {
        form.permissions = form.permissions.filter(p => !modActions.includes(p));
    }
}

console.log("Before click:", form.permissions);
toggleModuleAll('clientes', true);
console.log("After select all:", form.permissions);

const isAllSelected = Object.values(matrix['clientes'].actions).every(p => form.permissions.includes(p));
console.log("Is all selected:", isAllSelected);

