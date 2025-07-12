// Define una constante para la URL base de la API
const RUTA_GLOBAL = "http://3.149.80.182/api/";

// Define un objeto llamado HttpService que contiene varios métodos asíncronos para interactuar con la API
const HttpService =  {
    // Método para enviar datos al servidor utilizando la ruta especificada
    async registrar(ruta, datos) {
        // Realiza una petición POST a la URL formada por la base y la ruta
        let respuesta = await fetch(RUTA_GLOBAL + ruta, {
            method: "post", // Método HTTP POST
            body: JSON.stringify(datos), // Convierte los datos en una cadena JSON y los envía en el cuerpo de la petición
        });
        // Convierte la respuesta en formato JSON
        let resultado = await respuesta.json();
        return resultado;
    },

    // Método para obtener datos del servidor desde una ruta específica
    async obtener(ruta) {
        // Realiza una petición GET a la URL formada por la base y la ruta
        let respuesta = await fetch(RUTA_GLOBAL + ruta);
        // Convierte la respuesta en formato JSON
        let resultado = await respuesta.json();
        return resultado;
    },

    // Método para editar datos en el servidor enviándolos a una ruta específica
    async editar(ruta, datos) {
        // Realiza una petición POST (también se podría usar PUT según el caso)
        let respuesta = await fetch(RUTA_GLOBAL + ruta, {
            method: "post", // Método HTTP POST
            body: JSON.stringify(datos), // Convierte los datos en formato JSON
        });
        // Convierte la respuesta en formato JSON
        let resultado = await respuesta.json();
        return resultado;
    },

    // Método para eliminar datos enviando información al servidor
    async eliminar(ruta, datos) {
        // Realiza una petición POST, asumiendo que el backend maneja eliminaciones a través de este método
        let respuesta = await fetch(RUTA_GLOBAL + ruta, {
            method: "post", // Método HTTP POST
            body: JSON.stringify(datos), // Convierte los datos en JSON para enviarlos
        });
        // Convierte la respuesta en formato JSON
        let resultado = await respuesta.json();
        return resultado;
    },

    // Método para realizar consultas con datos adicionales en el cuerpo de la solicitud
    async obtenerConConsultas(ruta, payload) {
        // Realiza una petición POST a la URL con el payload especificado
        let respuesta = await fetch(RUTA_GLOBAL + ruta, {
            method: "post", // Método HTTP POST
            body: JSON.stringify(payload), // Convierte el payload en JSON
        });
        // Convierte la respuesta en formato JSON
        let resultado = await respuesta.json();
        return resultado;
    },

    // Método específico para obtener datos de negocio, reutilizando el método obtener
    obtenerDatosNegocio() {
        // Llama al método obtener con una ruta predefinida
        return this.obtener("configuracion/obtener_datos.php");
    }
}

// Exporta el objeto HttpService para que pueda ser utilizado en otras partes del proyecto
export default HttpService;
