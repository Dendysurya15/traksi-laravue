{
    "compilerOptions": {
        "target": "es5",
        "lib": ["es2015", "dom"],
        "module": "esnext",
        "moduleResolution": "node",
        "strict": true,
        "esModuleInterop": true,
        "skipLibCheck": true,
        "forceConsistentCasingInFileNames": true,
        "noImplicitReturns": true,
        "noImplicitAny": true,
        "noUnusedLocals": true,
        "noUnusedParameters": true,
        "declaration": false,
        "sourceMap": true,
        "outDir": "./dist",
        "baseUrl": ".",
        "paths": {
            "@/*": ["src/*"]
        }
    },
    "include": [
        "src/**/*.ts",
        "src/**/*.tsx",
        "src/**/*.vue"
    ],
    "exclude": [
        "node_modules"
    ]
}
