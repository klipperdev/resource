Klipper Resource Component
==========================

The Resource component is a resource management layer for Doctrine. This library has been
designed to facilitate the creation of a Batch API for processing a list of resources<sup>1</sup>
(ex. external data loader).

However, it is entirely possible to build an API Bulk above this library.

It allows to easily perform actions on Doctrine using the best practices automatically according
to selected options (flush for each resource or for all resources, but also skip errors of the
invalid resources), whether for a resource or set of resources.

Features include:

- Resource Domain Manager for get a resource domain for an Doctrine resource
- Resource Domain for each Doctrine resource for easy management:
  - generate new instance of resource with default value configured by Klipper Default Value
  - create one resource with validation (for object or Form instance)
  - create a list of resources with validation for each resource (for object or Form instance)
  - update one resource with validation (for object or Form instance)
  - update a list of resources with validation for each resource (for object or Form instance)
  - upsert one resource with validation (create or update for object or Form instance)
  - upsert a list of resources with validation for each resource (create or update for object or Form instance)
  - delete one resource with soft delete or hard delete for compatible resources
  - delete a list of resources with soft delete or hard delete for compatible resources
  - undelete one resource for compatible resources with soft delete
  - undelete a list of resources for compatible resources with soft delete
- Each resource domain allow:
  - to have the possibility to do an transaction with rollback for each resource of the list or for all resources in only one time
  - to have the possibility to skip the errors of an resource, and continue to run the rest of the list (compatible only with the transaction for each resource)
  - to return the list of resources with the status of the action (created, updated, error ...) on each resource of the list
- Request content converter:
  - JSON converter
  - XML converter
- Form handler to work with Symfony Form

> **Note:**
> <sup>1</sup> A resource is an Doctrine entity or Doctrine document

Resources
---------

- [Documentation](https://doc.klipper.dev/components/resource)
- [Report issues](https://github.com/klipperdev/klipper/issues)
  and [send Pull Requests](https://github.com/klipperdev/klipper/pulls)
  in the [main Klipper repository](https://github.com/klipperdev/klipper)
