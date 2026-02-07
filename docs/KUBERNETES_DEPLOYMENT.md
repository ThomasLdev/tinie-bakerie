# Kubernetes Deployment Guide

This guide documents deploying a Symfony/FrankenPHP application to a Raspberry Pi k3s cluster with GitOps (Flux).

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│ GitHub                                                       │
│                                                              │
│  ┌─────────────┐     ┌─────────────────────────────────┐   │
│  │ Application │────►│ GitHub Actions                   │   │
│  │ Code        │     │ - Build assets (x86)             │   │
│  └─────────────┘     │ - Build ARM64 image              │   │
│                      │ - Push to GHCR                    │   │
│  ┌─────────────┐     │ - Update k8s manifest            │   │
│  │ k8s/        │◄────│                                   │   │
│  │ manifests   │     └─────────────────────────────────┘   │
│  └──────┬──────┘                                            │
│         │                                                    │
│  ┌──────▼──────┐                                            │
│  │ GHCR        │                                            │
│  │ Images      │                                            │
│  └──────┬──────┘                                            │
└─────────┼────────────────────────────────────────────────────┘
          │
          │ Flux watches repo + pulls images
          ▼
┌─────────────────────┐
│ Raspberry Pi (k3s)  │
│ ┌─────────────────┐ │
│ │ Flux            │ │
│ │ PostgreSQL      │ │
│ │ App (FrankenPHP)│ │
│ └─────────────────┘ │
└─────────────────────┘
```

---

## 1. Raspberry Pi Setup

### 1.1 Enable cgroups (required for k3s)

Edit `/boot/cmdline.txt` and append to the existing line:
```
cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

Reboot:
```bash
sudo reboot
```

### 1.2 Install k3s

```bash
# Install k3s
curl -sfL https://get.k3s.io | sh -

# Configure kubectl for non-root user
mkdir -p ~/.kube
sudo cp /etc/rancher/k3s/k3s.yaml ~/.kube/config
sudo chown $(id -u):$(id -g) ~/.kube/config
echo 'export KUBECONFIG=~/.kube/config' >> ~/.bashrc
source ~/.bashrc

# Verify installation
kubectl get nodes
```

---

## 2. Local Development Setup

### 2.1 Docker Buildx (for cross-platform builds)

```bash
# Install QEMU for ARM64 emulation
docker run --privileged --rm tonistiigi/binfmt --install arm64

# Create multi-arch builder
docker buildx create --name multiarch --driver docker-container --bootstrap --use

# Verify ARM64 support
docker buildx inspect --bootstrap | grep linux/arm64
```

### 2.2 Build Assets Locally

Assets must be built locally before Docker build (avoids slow emulated builds):

```bash
cd ~/PhpstormProjects/tinie-bakerie

# Build assets using dev container
docker compose exec php bin/console tailwind:build --minify
docker compose exec php bin/console asset-map:compile
```

### 2.3 Build ARM64 Image

```bash
docker buildx build \
  --platform linux/arm64 \
  --target frankenphp_prod \
  -t tinie-bakerie:latest \
  --output type=docker,dest=/tmp/tinie-bakerie-arm64.tar \
  .
```

### 2.4 Manual Deploy (without CI/CD)

```bash
# Transfer image to Pi
scp /tmp/tinie-bakerie-arm64.tar pi@192.168.1.19:/tmp/

# Import into k3s
ssh pi@192.168.1.19 'sudo k3s ctr images import /tmp/tinie-bakerie-arm64.tar'

# Apply manifests
cat k8s/*.yaml | ssh pi@192.168.1.19 'KUBECONFIG=~/.kube/config kubectl apply -f -'

# Restart deployment
ssh pi@192.168.1.19 'KUBECONFIG=~/.kube/config kubectl rollout restart deployment/tinie-bakerie -n tinie-bakerie'
```

---

## 3. Kubernetes Manifests

All manifests are in `k8s/` directory:

| File | Purpose |
|------|---------|
| `namespace.yaml` | Isolated namespace for the app |
| `secrets.yaml` | Database credentials, APP_SECRET |
| `postgres.yaml` | PostgreSQL deployment + PVC + service |
| `app.yaml` | FrankenPHP deployment + service |
| `configmap.yaml` | Caddyfile configuration |

### 3.1 Apply Manifests Manually

```bash
# All at once (files must start with ---)
kubectl apply -f k8s/

# Or individually in order
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/secrets.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/postgres.yaml
kubectl apply -f k8s/app.yaml
```

### 3.2 Run Migrations

```bash
kubectl exec -it deploy/tinie-bakerie -n tinie-bakerie -- bin/console doctrine:migrations:migrate --no-interaction
```

---

## 4. Flux CD Setup (GitOps)

Flux watches the Git repository and automatically deploys changes.

### 4.1 Install Flux CLI on Pi

```bash
curl -s https://fluxcd.io/install.sh | sudo FLUX_VERSION=2.2.3 bash
```

### 4.2 Create GitHub Token

1. Go to: https://github.com/settings/tokens/new
2. Select scopes:
   - `repo` - For Flux to access repository
   - `read:packages` - For k3s to pull images from GHCR
3. Copy the token

### 4.3 Bootstrap Flux

```bash
export GITHUB_TOKEN=your_token_here

flux bootstrap github \
  --owner=ThomasLdev \
  --repository=tinie-bakerie \
  --branch=main \
  --path=./k8s \
  --personal
```

This will:
- Install Flux components on k3s
- Create a deploy key in your GitHub repo
- Configure Flux to watch `k8s/` directory

### 4.4 Create Image Pull Secret

Required for k3s to pull images from GHCR:

```bash
kubectl create secret docker-registry ghcr-secret \
  --namespace=tinie-bakerie \
  --docker-server=ghcr.io \
  --docker-username=ThomasLdev \
  --docker-password=$GITHUB_TOKEN
```

### 4.5 Verify Flux Status

```bash
# Check Flux components
flux get all

# Check if repo is synced
flux get sources git

# Check kustomizations
flux get kustomizations

# Force reconciliation
flux reconcile kustomization flux-system --with-source
```

---

## 5. GitHub Actions CI/CD

The workflow (`.github/workflows/deploy.yaml`) runs on push to `main`:

1. **Build assets** - Using x86 Docker (fast, native)
2. **Build ARM64 image** - Using QEMU emulation
3. **Push to GHCR** - `ghcr.io/thomasldev/tinie-bakerie`
4. **Update manifest** - Commits new image tag to `k8s/app.yaml`
5. **Flux deploys** - Detects git change, applies to cluster

### Required Permissions

The workflow uses `GITHUB_TOKEN` with:
- `contents: write` - To commit manifest changes
- `packages: write` - To push images to GHCR

No additional secrets needed.

---

## 6. Useful Commands

### k3s / kubectl

```bash
# View all resources in namespace
kubectl get all -n tinie-bakerie

# View pods with status
kubectl get pods -n tinie-bakerie -w

# View pod logs
kubectl logs deploy/tinie-bakerie -n tinie-bakerie -f

# Execute command in pod
kubectl exec -it deploy/tinie-bakerie -n tinie-bakerie -- bin/console cache:clear

# Restart deployment
kubectl rollout restart deployment/tinie-bakerie -n tinie-bakerie

# View deployment history
kubectl rollout history deployment/tinie-bakerie -n tinie-bakerie

# Rollback to previous version
kubectl rollout undo deployment/tinie-bakerie -n tinie-bakerie

# Scale deployment
kubectl scale deployment/tinie-bakerie -n tinie-bakerie --replicas=2

# Port forward for local access
kubectl port-forward svc/tinie-bakerie -n tinie-bakerie 8080:80

# View resource usage
kubectl top pods -n tinie-bakerie
```

### k3s specific

```bash
# Import image directly to k3s
sudo k3s ctr images import image.tar

# List images in k3s
sudo k3s ctr images list

# View k3s logs
sudo journalctl -u k3s -f

# Restart k3s
sudo systemctl restart k3s
```

### Flux

```bash
# Check overall status
flux get all

# Check sources
flux get sources git

# Force sync
flux reconcile kustomization flux-system --with-source

# Suspend/resume reconciliation
flux suspend kustomization flux-system
flux resume kustomization flux-system

# View Flux logs
flux logs -f

# Uninstall Flux
flux uninstall
```

### Docker Buildx

```bash
# List builders
docker buildx ls

# Create new builder
docker buildx create --name mybuilder --use

# Build for multiple platforms
docker buildx build --platform linux/amd64,linux/arm64 -t image:tag .

# Build and push
docker buildx build --platform linux/arm64 -t ghcr.io/user/image:tag --push .

# Build and export to tar
docker buildx build --platform linux/arm64 -t image:tag --output type=docker,dest=image.tar .
```

---

## 7. Adding a New Application

1. Create `k8s/` directory in your project with:
   - `namespace.yaml`
   - `secrets.yaml`
   - `app.yaml`
   - Any other resources (postgres, redis, etc.)

2. Copy `.github/workflows/deploy.yaml` and adjust:
   - Image name
   - Build commands

3. Flux will automatically detect new manifests in `k8s/`

4. Create namespace-specific image pull secret:
   ```bash
   kubectl create secret docker-registry ghcr-secret \
     --namespace=your-app \
     --docker-server=ghcr.io \
     --docker-username=ThomasLdev \
     --docker-password=$GITHUB_TOKEN
   ```

---

## 8. Troubleshooting

### Pod stuck in ImagePullBackOff

```bash
# Check events
kubectl describe pod -n tinie-bakerie <pod-name>

# Verify secret exists
kubectl get secret ghcr-secret -n tinie-bakerie

# Test image pull manually
sudo k3s ctr images pull ghcr.io/thomasldev/tinie-bakerie:latest
```

### Flux not syncing

```bash
# Check Flux logs
flux logs -f

# Check git source status
flux get sources git

# Force reconciliation
flux reconcile source git flux-system
flux reconcile kustomization flux-system
```

### Application errors

```bash
# Check pod logs
kubectl logs deploy/tinie-bakerie -n tinie-bakerie --previous

# Check events
kubectl get events -n tinie-bakerie --sort-by='.lastTimestamp'

# Shell into container
kubectl exec -it deploy/tinie-bakerie -n tinie-bakerie -- sh
```

---

## 9. Access

- **App URL**: http://192.168.1.19:30080
- **PostgreSQL**: Internal only (ClusterIP on port 5432)

---

## 10. Security Notes

- Secrets in `k8s/secrets.yaml` are base64 encoded, not encrypted
- For production, consider:
  - Sealed Secrets or SOPS for encrypted secrets
  - External secrets operator for vault integration
  - Network policies to restrict pod communication
