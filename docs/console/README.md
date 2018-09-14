# Console Commands
> Documentation is a WIP.


The following console tools are provided by this module and can be executed using the [Nails Console Tool](https://github.com/nails/module-console).


| Command                | Description                     |
|-------------------------|--------------------------------|
| `make:controller:admin` | Creates a new admin controller |


## Command Documentation



### `make:controller:admin [<controllerName>] [<methods>]`

Interactively generates new admin controllers which are powered by an existing model.

#### Arguments & Options

| Argument      | Description                                                                         | Required | Default |
|---------------|-------------------------------------------------------------------------------------|----------|---------|
| modelName     | The name of the model on which to base the controller. Specify multiples using CSV  | no       | null    |
| modelProvider | The provider of the model(s)                                                        | no       | app     |
