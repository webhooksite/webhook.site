# Webhook K8s Configuration Sample
The files in this directory should work with Minikube, A local Rancher Configurations, or Docker configuration on a local machine.  

To deploy this to a cluster or into a cloud K8s service you will need to change the ingress.yml and namespaces to match your configuration.

## Warning about the Namespace.yml
The namespace.yml will create a namespace for you.  This will work fine but it may make the namespace hard to find in tools like Rancher.  It is suggested that you create your own namespace and remove this file.

## Using these files
To use these files with kubectl do the following:
``` 
kubectl apply -f ./
```
If you created your own namespace then you will need to remover the namespace entries in the files and the namespace.yml before you run the same command but specifing the namespace to use for deployment:
```
kubectl apply -n <yournamespace> -f ./
```