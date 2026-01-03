# 🚀 Próximos Pasos Inmediatos

**Estado Actual:** Backend API 100% Implementado ✅  
**Siguiente:** Ejecutar Migraciones → Probar API → Implementar App Móvil

---

## 🎉 Lo que YA está hecho (Backend completo)

```
✅ AuthController con login/logout completo
✅ Middleware ApiTenancy implementado
✅ StudentApiController (GET/PATCH /profile)
✅ TrainingPlanApiController (GET /plans, /plans/current, /plans/{id})
✅ WorkoutApiController (POST/GET /workouts)
✅ Modelos Workout + WorkoutExercise
✅ Migraciones creadas
✅ Todas las rutas registradas en api.php
✅ Documentación completa (MOBILE_API_DOCUMENTATION.md)
```

**Tiempo invertido:** ~7 horas  
**Resultado:** API REST lista para recibir requests desde la app móvil

---

## HOY - Ejecutar y Probar (30-60 minutos)

### Paso 1: Ejecutar Migraciones (5 minutos)

```bash
# En tu terminal, dentro del proyecto FitTrack
php artisan tenants:migrate
```

Esto creará las tablas `workouts` y `workout_exercises` en cada base de datos de tenant.

**Verificar:**
```bash
# Ver que las migraciones se ejecutaron
php artisan tenants:list
```

---

### Paso 2: Configurar Acceso Local (5 minutos)

**IMPORTANTE:** Esta es una app multi-tenant. Tienes 2 opciones:

#### ✅ Opción A: Usar la API sin dominio (RECOMENDADO para desarrollo móvil)

Las rutas de API ya están configuradas con el middleware `ApiTenancy` que lee el header `X-Tenant-ID`, así que **NO necesitas configurar dominios**. Solo necesitas:

1. Tener el servidor corriendo:
```bash
php artisan serve
# O usa Laragon que ya tiene todo configurado en http://localhost
```

2. Acceder directamente a: `http://localhost/api/auth/login`

3. Para las demás rutas, incluir el header `X-Tenant-ID` (que obtienes del login)

**Ventaja:** Funciona directo, ideal para API móvil

---

#### Opción B: Usar dominios de tenant (para pruebas web)

Si prefieres usar dominios:

1. **Editar hosts** (Windows: `C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1 fittrack.test
127.0.0.1 fittrack_client1.fittrack.test
127.0.0.1 fittrack_client2.fittrack.test
```

2. Acceder a: `http://fittrack_client1.fittrack.test/api/profile`

---

### Paso 3: Probar API con Postman/Thunder Client (30 minutos)

#### A. Login (sin tenant, funciona directo)
```http
POST http://localhost/api/auth/login
Content-Type: application/json

{
  "email": "tu-usuario@example.com",
  "password": "tu-password"
}
```

**Esperado:** Respuesta con `tenant`, `user`, `student`, y `token`

**⚠️ IMPORTANTE:** Guarda estos valores de la respuesta:
- `token` → Para header `Authorization`
- `tenant.id` → Para header `X-Tenant-ID`

**Ejemplo de respuesta:**
```json
{
  "tenant": {
    "id": "fittrack_client1",  // ← GUARDA ESTO
    "name": "Client 1",
    "domain": "http://fittrack_client1.fittrack.test"
  },
  "user": { "id": 1, "email": "juan@example.com" },
  "student": { /* datos completos */ },
  "token": "1|abc123xyz..."  // ← GUARDA ESTO
}
```

---

#### B. Ver Perfil (requiere headers de autenticación)
### Día 1: Setup Inicial (2-3 horas)

#### A. Crear Proyecto Expo
```bash
# Crear proyecto
npx create-expo-app fittrack-mobile
cd fittrack-mobile

# Instalar dependencias
npm install axios @react-native-async-storage/async-storage
npm install @react-navigation/native @react-navigation/stack
npm install react-native-screens react-native-safe-area-context
```

#### B. Estructura de Carpetas
```
mkdir -p src/api src/context src/screens src/navigation src/components
```

Estructura final:
```
fittrack-mobile/
├── App.js
├── package.json
└── src/
    ├── api/
    │   ├── client.js
    │   ├── auth.js
    │   ├── profile.js
    │   ├── plans.js
    │   └── workouts.js
    ├── context/
    │   └── AuthContext.js
    ├── screens/
    │   ├── LoginScreen.js
    │   ├── HomeScreen.js
    │   ├── PlansScreen.js
    │   ├── PlanDetailScreen.js
    │   ├── WorkoutScreen.js
    │   └── ProfileScreen.js
    ├── navigation/
    │   └── RootNavigator.js
    └── components/
        └── (componentes reutilizables)
```

#### C. Configurar API Client

**Crear `src/api/client.js`:**
```javascript
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// ⚠️ IMPORTANTE: Cambiar según tu entorno
// Para desarrollo con Expo Go, usa la IP de tu computadora en la red local
// NO uses 'localhost' porque el teléfono no puede acceder a localhost de tu PC

// Opciones:
// 1. IP Local (RECOMENDADO para Expo Go):
const BASE_URL = 'http://192.168.1.10/api'; // Reemplaza con tu IP (usa ipconfig en Windows)

// 2. Laragon (si usas Laragon y configuraste hosts):
// const BASE_URL = 'http://fittrack.test/api';

// 3. ngrok (si necesitas exponer tu API a internet):
// const BASE_URL = 'https://tu-url.ngrok.io/api';

const apiClient = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor para agregar token y tenant
apiClient.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('token');
    const tenantId = await AsyncStorage.getItem('tenant_id');
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    if (tenantId) {
      config.headers['X-Tenant-ID'] = tenantId;
    }
    
    return config;
  },
  (error) => Promise.reject(error)
);

export default apiClient;
```

#### D. Crear AuthContext

**Crear `src/context/AuthContext.js`:**
```javascript
import React, { createContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../api/client';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [student, setStudent] = useState(null);
  const [tenant, setTenant] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const token = await AsyncStorage.getItem('token');
      const tenantId = await AsyncStorage.getItem('tenant_id');
      const userData = await AsyncStorage.getItem('user');
      const studentData = await AsyncStorage.getItem('student');
      const tenantData = await AsyncStorage.getItem('tenant');

      if (token && tenantId) {
        setUser(JSON.parse(userData));
        setStudent(JSON.parse(studentData));
        setTenant(JSON.parse(tenantData));
      }
    } catch (error) {
      console.error('Error checking auth:', error);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const response = await apiClient.post('/auth/login', { email, password });
      const { token, user, student, tenant } = response.data;

      // Guardar en AsyncStorage
      await AsyncStorage.setItem('token', token);
      await AsyncStorage.setItem('tenant_id', tenant.id);
      await AsyncStorage.setItem('user', JSON.stringify(user));
      await AsyncStorage.setItem('student', JSON.stringify(student));
      await AsyncStorage.setItem('tenant', JSON.stringify(tenant));

      // Actualizar estado
      setUser(user);
      setStudent(student);
      setTenant(tenant);

      return { success: true };
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.error || 'Error al iniciar sesión' 
      };
    }
  };

  const logout = async () => {
    try {
      await apiClient.post('/auth/logout');
    } catch (error) {
      console.error('Error logging out:', error);
    } finally {
      // Limpiar storage y estado
      await AsyncStorage.clear();
      setUser(null);
      setStudent(null);
      setTenant(null);
    }
  };

  return (
    <AuthContext.Provider value={{ user, student, tenant, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};
```

#### E. Crear Login Screen

**Crear `src/screens/LoginScreen.js`:**
```javascript
import React, { useState, useContext } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Alert } from 'react-native';
import { AuthContext } from '../context/AuthContext';

export default function LoginScreen() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useContext(AuthContext);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Por favor completa todos los campos');
      return;
    }

    setLoading(true);
    const result = await login(email, password);
    setLoading(false);

    if (!result.success) {
      Alert.alert('Error', result.error);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>FitTrack</Text>
      
      <TextInput
        style={styles.input}
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        keyboardType="email-address"
        autoCapitalize="none"
      />
      
      <TextInput
        style={styles.input}
        placeholder="Contraseña"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
      />
      
      <TouchableOpacity 
        style={styles.button} 
        onPress={handleLogin}
        disabled={loading}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Cargando...' : 'Iniciar Sesión'}
        </Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 20,
    backgroundColor: '#fff',
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    marginBottom: 40,
    textAlign: 'center',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    padding: 15,
    marginBottom: 15,
    borderRadius: 8,
  },
  button: {
    backgroundColor: '#007AFF',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});
```

#### F. Configurar App.js Principal

**Editar `App.js`:**
```javascript
import React, { useContext } from 'react';
import { AuthProvider, AuthContext } from './src/context/AuthContext';
import LoginScreen from './src/screens/LoginScreen';
import HomeScreen from './src/screens/HomeScreen'; // Crear después

function AppNavigator() {
  const { user, loading } = useContext(AuthContext);

  if (loading) {
    return null; // O un loading screen
  }

  return user ? <HomeScreen /> : <LoginScreen />;
}

export default function App() {
  return (
    <AuthProvider>
      <AppNavigator />
    </AuthProvider>
  );
}
```

#### G. Probar Login

```bash
# Ejecutar app
npx expo start

# En otro dispositivo:
# - Instala Expo Go desde App Store/Play Store
# - Escanea el QR que aparece en la terminal
```

**⚠️ IMPORTANTE:** 
- Cambia `BASE_URL` en `client.js` a tu IP local
- Asegúrate de que tu teléfono y computadora estén en la misma red
- Usa una cuenta de usuario que exista en tu base de datos

---

### Día 2-3: Screens Principales (8-10 horas)

Ahora que el login funciona, crear el resto de las pantallas:

#### Pantallas a Implementar:

**1. HomeScreen** (2 horas)
- Mostrar nombre del estudiante
- Mostrar plan actual
- Mostrar últimas sesiones
- Navegación a otras pantallas

**2. PlansScreen** (2 horas)
- Listar todos los planes del estudiante
- Mostrar detalles básicos (nombre, fecha, objetivo)
- Link a PlanDetailScreen

**3. PlanDetailScreen** (2 horas)
- Mostrar ejercicios del plan
- Detalles: series, reps, peso, notas
- Botón para iniciar workout

**4. WorkoutScreen** (2-3 horas)
- Form para registrar ejercicios
- Inputs: sets completados, reps, peso
- Timer opcional
- Botón guardar sesión

**5. ProfileScreen** (1 hora)
- Mostrar datos del perfil
- Permitir editar algunos campos
- Botón de logout

**6. Navigation** (1 hora)
- React Navigation setup
- Tab navigation o Drawer
- Stack para detalles

---

## Recursos y Referencias
       │   └── LoginScreen.js
       └── App.js

[ ] 9. Prueba:
       npx expo start
       (Escanea QR con Expo Go en teléfono)
```

---

## Mañana (4 horas)

### Backend: Implementar Controladores de API

```
[ ] 1. Lee MOBILE_API_IMPLEMENTATION_PLAN.md Fase 2 y 3
[ ] 2. Abre MOBILE_API_CODIGO_READY.md Sección 4-6
[ ] 3. Crea: app/Http/Controllers/Api/StudentApiController.php
       └─ Copia Sección 4

[ ] 4. Crea: app/Http/Controllers/Api/TrainingPlanApiController.php
       └─ Copia Sección 5

[ ] 5. Actualiza: routes/api.php
       └─ Copia Sección 6

[ ] 6. Prueba en Postman (con token del login):
       GET localhost:8000/api/profile
       Headers:
         Authorization: Bearer 1|abc...
         X-Tenant-ID: tu-tenant-uuid
       
       DEBE RETORNAR: datos del estudiante
```

### Frontend: Expandir Screens

```
[ ] 1. Copia Sección 8 (AuthContext mejorado)
[ ] 2. Crea src/api/profile.js (GET/PATCH /profile)
[ ] 3. Crea src/api/plans.js (GET /plans)
[ ] 4. Crea src/screens/HomeScreen.js
       └─ Mostrar nombre del alumno
       └─ Mostrar últimas sesiones

[ ] 5. Crea src/screens/PlansScreen.js
       └─ Listar planes del alumno
       └─ Link a PlanDetailScreen

[ ] 6. Crea src/navigation/RootNavigator.js
       └─ Login Stack / App Stack
```

---

## Días 3-5 (6-8 horas)

### Backend: Models y Workouts

```
[ ] 1. Crea: app/Models/Tenant/Workout.php
[ ] 2. Crea: app/Models/Tenant/WorkoutExercise.php
[ ] 3. Crea migraciones:
       php artisan make:migration create_workouts_table --path database/migrations/tenant
       php artisan make:migration create_workout_exercises_table --path database/migrations/tenant

[ ] 4. Ejecuta migraciones:
       php artisan migrate

[ ] 5. Crea: app/Http/Controllers/Api/WorkoutApiController.php
[ ] 6. Prueba POST /api/workouts en Postman
```

### Frontend: Workout Registration

```
[ ] 1. Crea src/screens/WorkoutScreen.js
       └─ Mostrar ejercicios del plan
       └─ Inputs para: sets, reps, peso
       └─ Botón "Guardar Sesión"

[ ] 2. Crea src/api/workouts.js (POST/GET)
[ ] 3. Integra con navigation
[ ] 4. Prueba flujo completo:
       Login → Home → Ver Planes → Abrir Plan → Registrar Sesión
```

---

## Semana 2 (2-3 horas)

### Documentación y Polish

## 📚 Documentos de Referencia

**📖 Documentación Completa:**
- [MOBILE_API_DOCUMENTATION.md](./MOBILE_API_DOCUMENTATION.md) - Todos los endpoints con ejemplos
- [RESUMEN_MOBILE_API.md](./RESUMEN_MOBILE_API.md) - Estado actual y checklist

**🔍 Consultar cuando necesites:**
- Ver estructura de requests/responses → MOBILE_API_DOCUMENTATION.md
- Ver qué está hecho y qué falta → RESUMEN_MOBILE_API.md
- Ejemplos de código Expo → Este documento (secciones anteriores)

---

## 🎯 Timeline Actualizado

| Día | Tarea | Tiempo | Estado |
|-----|-------|--------|--------|
| ✅ Completado | Backend API completo | 7 horas | ✅ HECHO |
| **HOY** | Ejecutar migraciones + Probar API | 30-60 min | 🔄 HACER |
| **Día 1** | Setup Expo + Login screen | 2-3 horas | ⚪ PENDIENTE |
| **Día 2-3** | Screens principales | 8-10 horas | ⚪ PENDIENTE |
| **Día 4-5** | Navigation + UX polish | 2-3 horas | ⚪ PENDIENTE |
| **Día 6+** | Testing + refinamiento | 2-3 horas | ⚪ PENDIENTE |

**Tiempo total:** ~20-25 horas (Backend + Frontend + Testing)

---

## ✅ Checklist General

### ✅ Backend (COMPLETADO)
- [x] Autenticación (login/logout)
- [x] Middleware de tenancy
- [x] API de perfil
- [x] API de planes
- [x] API de workouts
- [x] Modelos y migraciones
- [x] Documentación

### 🔄 Migraciones y Testing (HOY)
- [ ] Ejecutar `php artisan tenants:migrate`
- [ ] Probar todos los endpoints en Postman
- [ ] Verificar respuestas correctas

### ⚪ App Móvil (SIGUIENTE)
- [ ] Setup proyecto Expo
- [ ] Configurar API client
- [ ] Implementar AuthContext
- [ ] Crear LoginScreen
- [ ] Crear HomeScreen
- [ ] Crear PlansScreen
- [ ] Crear PlanDetailScreen
- [ ] Crear WorkoutScreen
- [ ] Crear ProfileScreen
- [ ] Configurar Navigation
- [ ] Testing en dispositivos
